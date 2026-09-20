<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\BranchContext;
use App\Support\BranchProductSync;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductCrudController extends Controller
{
    public function index(): View
    {
        $products = BranchContext::scope(DB::table('pos_products'))->orderByDesc('id')->get();
        return view('pos_admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('pos_admin.products.create');
    }

    public function importForm(): View
    {
        return view('pos_admin.products.import');
    }

    public function downloadImportTemplate(): BinaryFileResponse
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['code', 'name', 'description', 'cost_price', 'price', 'stock', 'image'],
            ['SKU-001', 'Example Product', 'Product description', 15.00, 25.00, 10, ''],
        ]);

        $path = storage_path('app/product-import-template.xlsx');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($path);

        return response()->download($path, 'onje-casa-products-template.xlsx')->deleteFileAfterSend(true);
    }

    public function import(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'max:51200'],
            'import_type' => ['required', 'in:spreadsheet,sql'],
            'sync_to_website' => ['nullable', 'boolean'],
        ]);

        $extension = strtolower($request->file('file')->getClientOriginalExtension());
        if (! in_array($extension, ['csv', 'txt', 'xls', 'xlsx', 'sql'], true)) {
            return back()->with('error', 'Upload a CSV, Excel, or MySQL SQL file.')->withInput();
        }

        $branchId = BranchContext::activeId();
        if (! $branchId) {
            return back()->with('error', 'Select a branch before importing products.');
        }

        try {
            $result = $data['import_type'] === 'sql'
                ? $this->importSql($request->file('file'), $branchId)
                : $this->importSpreadsheet($request->file('file'), $branchId, $request->boolean('sync_to_website'));
        } catch (\Throwable $exception) {
            report($exception);
            return back()->with('error', 'Import failed: ' . $exception->getMessage());
        }

        return redirect()->route('pos.admin.products.index')->with('success', $result);
    }

    private function importSpreadsheet($file, int $branchId, bool $syncToWebsite): string
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $headers = $this->importHeaders(array_shift($rows) ?: []);
        $required = ['name', 'price', 'stock'];
        if (array_diff($required, array_keys($headers))) {
            throw new \RuntimeException('Spreadsheet must include name, price, and stock columns.');
        }

        $codeCounts = collect($rows)
            ->map(fn (array $row) => trim((string) ($row[$headers['code']] ?? '')))
            ->filter()
            ->countBy();
        $created = 0;
        $updated = 0;
        DB::transaction(function () use ($rows, $headers, $codeCounts, $branchId, $syncToWebsite, &$created, &$updated) {
            foreach ($rows as $number => $row) {
                $values = $this->importRow($row, $headers);
                if (trim((string) ($values['name'] ?? '')) === '') {
                    continue;
                }

                $code = trim((string) ($values['code'] ?? ''));
                $roundedBarcode = preg_match('/^\d{12,}0{5,}$/', $code) === 1;
                $repeatedCode = $code !== '' && ($codeCounts[$code] ?? 0) > 1;
                if ($code === '' || $roundedBarcode || $repeatedCode || preg_match('/^\d+(?:\.\d+)?E\+\d+$/i', $code)) {
                    $code = $this->makeUniqueCode((string) $values['name'], $branchId);
                }
                $existing = DB::table('pos_products')->where('branch_id', $branchId)->where('code', $code)->first();
                $costValue = $values['cost_price'] ?? null;
                $payload = [
                    'branch_id' => $branchId,
                    'code' => $code,
                    'name' => trim((string) $values['name']),
                    'description' => trim((string) ($values['description'] ?? '')),
                    'cost_price' => blank($costValue) ? 0 : $this->importNumber($costValue, 'cost price', $number + 2),
                    'price' => $this->importNumber($values['price'] ?? null, 'selling price', $number + 2),
                    'stock' => $this->importInteger($values['stock'] ?? null, 'stock', $number + 2),
                    'image' => trim((string) ($values['image'] ?? '')) ?: null,
                    'updated_at' => now(),
                ];

                if ($existing) {
                    DB::table('pos_products')->where('id', $existing->id)->update($payload);
                    $updated++;
                    $productId = (int) $existing->id;
                } else {
                    $payload['created_at'] = now();
                    $productId = (int) DB::table('pos_products')->insertGetId($payload);
                    $created++;
                }

                $product = DB::table('pos_products')->where('id', $productId)->first();
                BranchProductSync::restore($branchId, $code);
                BranchProductSync::syncProductToBranches($product, syncStock: true);
                if ($syncToWebsite) {
                    $this->syncPosProductToWebsite($productId, false);
                }
            }
        });

        return "Product import complete: {$created} created, {$updated} updated.";
    }

    private function importSql($file, int $branchId): string
    {
        $sql = file_get_contents($file->getRealPath());
        $sql = preg_replace('/^\\s*(--|#).*$/m', '', (string) $sql);
        $sql = trim((string) $sql);
        if ($sql === '' || ! preg_match('/\\b(?:INSERT|REPLACE)\\s+(?:IGNORE\\s+)?INTO\\s+[`"]?pos_products[`"]?/i', $sql)) {
            throw new \RuntimeException('SQL file must contain INSERT or REPLACE statements for pos_products.');
        }
        if (preg_match('/\\b(?:DROP|ALTER|TRUNCATE|DELETE|UPDATE|CREATE|GRANT|REVOKE)\\b/i', $sql)) {
            throw new \RuntimeException('SQL import only accepts product INSERT or REPLACE statements.');
        }

        $statements = array_filter(array_map('trim', preg_split('/;\\s*(?:\r?\n|$)/', $sql)));
        $count = 0;
        DB::transaction(function () use ($statements, $branchId, &$count) {
            foreach ($statements as $statement) {
                if (! preg_match('/^\\s*(?:INSERT|REPLACE)\\s+(?:IGNORE\\s+)?INTO\\s+[`"]?pos_products[`"]?/i', $statement)) {
                    throw new \RuntimeException('SQL file contains an unsupported statement.');
                }
                DB::unprepared($statement);
                $count++;
            }
            DB::table('pos_products')->whereNull('branch_id')->update(['branch_id' => $branchId]);
        });

        return "SQL product import complete: {$count} statement(s) executed.";
    }

    private function importHeaders(array $row): array
    {
        $headers = [];
        foreach ($row as $column => $value) {
            $key = Str::of((string) $value)->lower()->replace([' ', '-'], '_')->toString();
            $key = [
                'total_stock' => 'stock',
                'quantity' => 'stock',
                'qty' => 'stock',
                'selling_price' => 'price',
                'unit_price' => 'price',
                'cost' => 'cost_price',
                'unit_cost' => 'cost_price',
                'sku' => 'code',
                'barcode' => 'code',
            ][$key] ?? $key;
            if ($key !== '') {
                $headers[$key] = $column;
            }
        }
        return $headers;
    }

    private function importRow(array $row, array $headers): array
    {
        $values = [];
        foreach ($headers as $key => $column) {
            $values[$key] = $row[$column] ?? null;
        }
        return $values;
    }

    private function importNumber(mixed $value, string $field, int $line): float
    {
        $value = str_replace([',', 'GHC', 'GH₵'], '', trim((string) $value));
        if ($value === '' || ! is_numeric($value) || (float) $value < 0) {
            throw new \RuntimeException("Invalid {$field} on row {$line}.");
        }
        return (float) $value;
    }

    private function importInteger(mixed $value, string $field, int $line): int
    {
        if ($value === '' || ! is_numeric($value) || (float) $value < 0 || floor((float) $value) !== (float) $value) {
            throw new \RuntimeException("Invalid {$field} on row {$line}.");
        }
        return (int) $value;
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'],
            'sync_to_website' => ['nullable', 'boolean'],
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = Str::uuid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('assets/admin/img/products'), $imageName);
        }

        $branchId = BranchContext::activeId();
        if (! $branchId) {
            return redirect()->back()->withInput()->with('error', 'Select a branch before creating a POS product.');
        }

        $code = $data['code'] ?: $this->makeUniqueCode($data['name'], $branchId);
        $existingCode = DB::table('pos_products')
            ->where('branch_id', $branchId)
            ->where('code', $code)
            ->exists();

        if ($existingCode) {
            return redirect()->back()->withInput()->with('error', 'Another product in this branch already uses that SKU/code.');
        }

        $productId = DB::table('pos_products')->insertGetId([
            'branch_id' => $branchId,
            'code' => $code,
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'cost_price' => $data['cost_price'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'image' => $imageName,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($request->has('sync_to_website')) {
            $this->syncPosProductToWebsite($productId, true);
        }

        $product = DB::table('pos_products')->where('id', $productId)->first();
        BranchProductSync::restore($branchId, $code);
        BranchProductSync::syncProductToBranches($product, syncStock: true);

        AuditTrail::record('product_created', 'Created product ' . $data['name'], [
            'auditable_type' => 'product',
            'auditable_id' => $productId,
            'properties' => ['product' => $data],
        ]);

        return redirect()->route('pos.admin.products.index')->with('success', 'Product Added');
    }

    public function edit(int $id): View
    {
        $product = BranchContext::scope(DB::table('pos_products'))->where('id', $id)->first();
        abort_unless($product, 404);
        return view('pos_admin.products.edit', compact('product'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $product = BranchContext::scope(DB::table('pos_products'))->where('id', $id)->first();
        abort_unless($product, 404);
        $oldCode = (string) $product->code;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'],
            'sync_to_website' => ['nullable', 'boolean'],
        ]);

        $imageName = $product->image ?? null;
        if ($request->hasFile('image')) {
            $imageName = Str::uuid() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->move(public_path('assets/admin/img/products'), $imageName);
        }

        $existingCode = DB::table('pos_products')
            ->where('branch_id', $product->branch_id)
            ->where('code', $data['code'])
            ->where('id', '<>', $id)
            ->exists();

        if ($existingCode) {
            return redirect()->back()->withInput()->with('error', 'Another product in this branch already uses that SKU/code.');
        }

        BranchContext::scope(DB::table('pos_products'))->where('id', $id)->update([
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'cost_price' => $data['cost_price'],
            'price' => $data['price'],
            'stock' => $data['stock'],
            'image' => $imageName,
            'updated_at' => now(),
        ]);

        if ($request->has('sync_to_website')) {
            $this->syncPosProductToWebsite($id, $request->hasFile('image'));
        }

        $updatedProduct = DB::table('pos_products')->where('id', $id)->first();
        BranchProductSync::syncProductToBranches($updatedProduct, $oldCode, false);

        AuditTrail::record('product_updated', 'Updated product ' . $data['name'], [
            'auditable_type' => 'product',
            'auditable_id' => $id,
            'properties' => [
                'before' => (array) $product,
                'after' => [
                    'code' => $data['code'],
                    'name' => $data['name'],
                    'description' => $data['description'] ?? '',
                    'cost_price' => $data['cost_price'],
                    'price' => $data['price'],
                    'stock' => $data['stock'],
                    'image' => $imageName,
                ],
            ],
        ]);

        return redirect()->route('pos.admin.products.index')->with('success', 'Product Updated');
    }

    public function destroy(int $id): RedirectResponse
    {
        $product = BranchContext::scope(DB::table('pos_products'))->where('id', $id)->first();
        if ($product && $product->branch_id && $product->code) {
            BranchProductSync::markRemoved((int) $product->branch_id, (string) $product->code, auth()->id());
        }

        BranchContext::scope(DB::table('pos_products'))->where('id', $id)->delete();
        AuditTrail::record('product_deleted', 'Deleted product ' . ($product->name ?? '#' . $id), [
            'auditable_type' => 'product',
            'auditable_id' => $id,
            'properties' => ['product' => $product ? (array) $product : null],
        ]);

        return redirect()->route('pos.admin.products.index')->with('success', 'Deleted');
    }

    private function makeUniqueCode(string $name, int $branchId): string
    {
        $base = Str::upper(Str::slug($name, ''));
        $base = Str::limit($base ?: 'PRD', 8, '');
        $code = $base . '-' . random_int(1000, 9999);

        while (DB::table('pos_products')->where('branch_id', $branchId)->where('code', $code)->exists()) {
            $code = $base . '-' . random_int(1000, 9999);
        }

        return $code;
    }

    private function syncPosProductToWebsite(int $posProductId, bool $refreshImage = false): void
    {
        $posProduct = BranchContext::scope(DB::table('pos_products'))->where('id', $posProductId)->first();
        if (! $posProduct) {
            return;
        }

        $websiteProduct = $posProduct->website_product_id
            ? DB::table('product_pages')->where('id', $posProduct->website_product_id)->first()
            : null;

        $picture = $websiteProduct->picture ?? null;
        if (($refreshImage || ! $picture) && $posProduct->image) {
            $picture = $this->copyPosImageToWebsite($posProduct->image) ?? $picture;
        }

        $payload = [
            'name' => $posProduct->name,
            'description' => $websiteProduct->description ?? 'POS Product',
            'contents' => $posProduct->description ?: $posProduct->name,
            'price' => $posProduct->price,
            'picture' => $picture,
            'pos_product_id' => $posProduct->id,
            'updated_at' => now(),
        ];

        if ($websiteProduct) {
            DB::table('product_pages')->where('id', $websiteProduct->id)->update($payload);
            return;
        }

        $payload['created_at'] = now();
        $websiteProductId = DB::table('product_pages')->insertGetId($payload);
        DB::table('pos_products')->where('id', $posProduct->id)->update([
            'website_product_id' => $websiteProductId,
            'updated_at' => now(),
        ]);
    }

    private function copyPosImageToWebsite(?string $imageName): ?string
    {
        $source = public_path('assets/admin/img/products/' . ltrim((string) $imageName, '/'));

        if (! is_file($source)) {
            return null;
        }

        $destination = public_path('uploads');
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $extension = pathinfo($source, PATHINFO_EXTENSION) ?: 'jpg';
        $fileName = Str::uuid() . '.' . $extension;
        copy($source, $destination . DIRECTORY_SEPARATOR . $fileName);

        return 'uploads/' . $fileName;
    }
}
