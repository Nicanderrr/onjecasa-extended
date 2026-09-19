<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ProductPage;
use App\Support\BranchProductSync;
use App\Support\ProductOptions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PDO;

class ImportNewPosProductsSeeder extends Seeder
{
    private string $sourceProject = 'C:\\xampp\\htdocs\\newPOS';

    public function run(): void
    {
        $source = $this->sourceConnection();
        $sourceProducts = $source
            ->query('SELECT code, name, description, price, stock, image, created_at, updated_at FROM pos_products ORDER BY id')
            ->fetchAll(PDO::FETCH_ASSOC);

        if (empty($sourceProducts)) {
            $this->command?->warn('No products found in the source newPOS database.');
            return;
        }

        $branchId = $this->branchId();
        $importedPosIds = [];
        $importedWebsiteIds = [];

        DB::transaction(function () use ($sourceProducts, $branchId, &$importedPosIds, &$importedWebsiteIds) {
            foreach ($sourceProducts as $sourceProduct) {
                $imageName = $this->copyProductImage($sourceProduct['image'] ?? null);
                $category = $this->categoryFor((string) $sourceProduct['name']);
                $description = trim((string) ($sourceProduct['description'] ?? ''));
                $contents = $description !== ''
                    ? $description
                    : 'Imported from newPOS with barcode/code ' . $sourceProduct['code'] . '.';

                Category::firstOrCreate(['name' => $category]);
                if ($branchId && DB::getSchemaBuilder()->hasTable('pos_categories')) {
                    DB::table('pos_categories')->updateOrInsert(
                        ['branch_id' => $branchId, 'name' => $category],
                        [
                            'code' => $this->posCategoryCode($category, (int) $branchId),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }

                $existingPos = DB::table('pos_products')
                    ->where('branch_id', $branchId)
                    ->where('code', $sourceProduct['code'])
                    ->first();
                $websiteProduct = $existingPos?->website_product_id
                    ? ProductPage::find($existingPos->website_product_id)
                    : ProductPage::where('name', $sourceProduct['name'])->first();

                if (! $websiteProduct) {
                    $websiteProduct = new ProductPage();
                }

                $websiteProduct->fill([
                    'name' => $sourceProduct['name'],
                    'description' => $category,
                    'contents' => $contents,
                    'price' => $sourceProduct['price'],
                    'stock' => (int) $sourceProduct['stock'],
                    'sizes' => $this->unitOptionsFor((string) $sourceProduct['name'], (float) $sourceProduct['price']),
                    'colors' => $this->addOnsFor((string) $sourceProduct['name']),
                    'features' => [
                        'Imported from newPOS',
                        'Barcode/code: ' . $sourceProduct['code'],
                        'Available in POS and storefront',
                    ],
                    'picture' => $imageName ? 'assets/admin/img/products/' . $imageName : 'images/onjecasa-products.svg',
                ])->save();

                $payload = [
                    'branch_id' => $branchId,
                    'website_product_id' => $websiteProduct->id,
                    'code' => $sourceProduct['code'],
                    'name' => $sourceProduct['name'],
                    'description' => $description,
                    'price' => $sourceProduct['price'],
                    'stock' => (int) $sourceProduct['stock'],
                    'image' => $imageName,
                    'created_at' => $sourceProduct['created_at'] ?: now(),
                    'updated_at' => $sourceProduct['updated_at'] ?: now(),
                ];

                if ($existingPos) {
                    unset($payload['created_at']);
                    DB::table('pos_products')->where('id', $existingPos->id)->update($payload);
                    $posId = $existingPos->id;
                } else {
                    $posId = DB::table('pos_products')->insertGetId($payload);
                }

                if ($branchId) {
                    BranchProductSync::restore((int) $branchId, (string) $sourceProduct['code']);
                }

                if ((int) ($websiteProduct->pos_product_id ?? 0) !== (int) $posId) {
                    $websiteProduct->forceFill(['pos_product_id' => $posId])->save();
                }

                $importedPosIds[] = (int) $posId;
                $importedWebsiteIds[] = (int) $websiteProduct->id;
            }

            $this->removeUnreferencedDemoRows($importedPosIds, $importedWebsiteIds);
        });

        if ($branchId) {
            BranchProductSync::syncAllBranchesFromCatalog((int) $branchId);
        }

        $this->command?->info('Imported ' . count($sourceProducts) . ' products from newPOS.');
    }

    private function sourceConnection(): PDO
    {
        $env = $this->readSourceEnv();
        $host = $env['DB_HOST'] ?? '127.0.0.1';
        $port = $env['DB_PORT'] ?? '3306';
        $database = $env['DB_DATABASE'] ?? 'rposystem';
        $username = $env['DB_USERNAME'] ?? 'root';
        $password = $env['DB_PASSWORD'] ?? '';

        return new PDO(
            "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    private function readSourceEnv(): array
    {
        $path = $this->sourceProject . DIRECTORY_SEPARATOR . '.env';
        if (! is_file($path)) {
            return [];
        }

        $values = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
        }

        return $values;
    }

    private function branchId(): ?int
    {
        if (! DB::getSchemaBuilder()->hasTable('branches')) {
            return null;
        }

        $branchId = DB::table('branches')->where('code', 'ALHAJI')->value('id')
            ?: DB::table('branches')->value('id');

        return $branchId ? (int) $branchId : null;
    }

    private function copyProductImage(?string $imageName): ?string
    {
        $imageName = trim((string) $imageName);
        if ($imageName === '') {
            return null;
        }

        $source = $this->sourceProject . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $imageName;
        if (! is_file($source)) {
            return $imageName;
        }

        $destination = public_path('assets/admin/img/products');
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        copy($source, $destination . DIRECTORY_SEPARATOR . $imageName);

        return $imageName;
    }

    private function removeUnreferencedDemoRows(array $importedPosIds, array $importedWebsiteIds): void
    {
        if (DB::getSchemaBuilder()->hasTable('pos_order_items')) {
            $referencedPosIds = DB::table('pos_order_items')->pluck('product_id')->map(fn ($id) => (int) $id)->all();
            DB::table('pos_products')
                ->whereNotIn('id', $importedPosIds)
                ->whereNotIn('id', $referencedPosIds ?: [0])
                ->where('code', 'like', 'JKS-%')
                ->delete();
        }

        if (DB::getSchemaBuilder()->hasTable('carts')) {
            DB::table('carts')->whereNotIn('product_id', $importedWebsiteIds ?: [0])->delete();
        }

        DB::table('product_pages')
            ->whereNotIn('id', $importedWebsiteIds)
            ->where(function ($query) {
                $query->whereNull('picture')
                    ->orWhere('picture', 'images/onjecasa-products.svg');
            })
            ->delete();
    }

    private function categoryFor(string $name): string
    {
        $lower = strtolower($name);

        if (str_contains($lower, 'aqua') || str_contains($lower, 'fanta') || str_contains($lower, 'coke') || str_contains($lower, 'vitamilk') || str_contains($lower, 'active')) {
            return 'Beverages';
        }

        if (str_contains($lower, 'mouth') || str_contains($lower, 'spray')) {
            return 'Personal Care';
        }

        if (str_contains($lower, 'staple')) {
            return 'Household';
        }

        return 'Groceries';
    }

    private function posCategoryCode(string $category, int $branchId): string
    {
        $existing = DB::table('pos_categories')
            ->where('branch_id', $branchId)
            ->where('name', $category)
            ->value('code');

        if ($existing) {
            return (string) $existing;
        }

        $base = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $category), 0, 3)) ?: 'CAT';

        return DB::table('pos_categories')->where('code', $base)->exists()
            ? $base . '-' . $branchId
            : $base;
    }

    private function unitOptionsFor(string $name, float $price): array
    {
        $lower = strtolower($name);

        if (str_contains($lower, 'aqua') || str_contains($lower, 'fanta') || str_contains($lower, 'coke') || str_contains($lower, 'vitamilk') || str_contains($lower, 'active')) {
            $options = ['Single Bottle', '6 Pack', '12 Pack', 'Carton'];
        } elseif (str_contains($lower, 'staple')) {
            $options = ['Single Item', 'Small Pack', 'Box'];
        } else {
            $options = ['Single Item', 'Small Pack', 'Family Pack', 'Carton'];
        }

        return ProductOptions::normalizeForStorage($options, [], $price);
    }

    private function addOnsFor(string $name): array
    {
        $lower = strtolower($name);

        if (str_contains($lower, 'aqua') || str_contains($lower, 'fanta') || str_contains($lower, 'coke') || str_contains($lower, 'vitamilk') || str_contains($lower, 'active')) {
            return ['Carrier Bag', 'Ice Pack', 'Delivery Crate'];
        }

        if (str_contains($lower, 'spray')) {
            return ['Carrier Bag', 'Gift Wrap'];
        }

        return ['Carrier Bag', 'Delivery Crate'];
    }
}
