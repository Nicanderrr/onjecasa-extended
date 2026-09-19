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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
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
