<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ProductPage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupermarketConversionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        if (DB::getSchemaBuilder()->hasTable('pos_settings')) {
            foreach ([
                'system_name' => 'ONJECASA',
                'receipt_header' => 'ONJECASA',
                'receipt_footer' => 'Thank you for shopping with us.',
                'business_phone' => '+233 57 833 9542',
                'business_address' => 'Nii Okaiman West Main Road, Greater Accra',
            ] as $key => $value) {
                DB::table('pos_settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => $now, 'created_at' => $now]
                );
            }
        }

        if (DB::getSchemaBuilder()->hasTable('branches')) {
            DB::table('branches')->where('code', 'ALHAJI')->update([
                'name' => 'ONJECASA - Alhaji',
                'updated_at' => $now,
            ]);
        }

        foreach (['Rice Products', 'Salads', 'Sides', 'Combos', 'Delivery Specials'] as $oldCategory) {
            Category::where('name', $oldCategory)->delete();
        }

        $branchId = DB::table('branches')->where('code', 'ALHAJI')->value('id');
        foreach (['Groceries', 'Beverages', 'Household', 'Personal Care', 'Add-ons', 'Fresh Produce', 'Bulk Deals'] as $category) {
            Category::firstOrCreate(['name' => $category]);

            if ($branchId && DB::getSchemaBuilder()->hasTable('pos_categories')) {
                DB::table('pos_categories')->updateOrInsert(
                    ['branch_id' => $branchId, 'name' => $category],
                    ['code' => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $category), 0, 3)) ?: 'CAT', 'updated_at' => $now, 'created_at' => $now]
                );
            }
        }

        if (DB::getSchemaBuilder()->hasTable('meal_extras')) {
            DB::table('meal_extras')->delete();
            DB::table('meal_extras')->insert($this->addOns($now));
        }

        $oldNames = [
            'Jollof Rice with Chicken',
            'Fried Rice with Chicken',
            'Waakye Special',
            'Plain Rice with Stew',
            'Grilled Chicken Salad',
            'ONJECASA Mixed Salad',
            'Extra Chicken',
            'Group Rice Combo',
        ];

        foreach ($this->products() as $index => $product) {
            $websiteProduct = ProductPage::where('name', $oldNames[$index] ?? null)->first()
                ?: ProductPage::where('name', $product['name'])->first()
                ?: new ProductPage();

            $websiteProduct->fill([
                'name' => $product['name'],
                'description' => $product['category'],
                'contents' => $product['description'],
                'price' => $product['price'],
                'stock' => $product['stock'],
                'picture' => 'images/onjecasa-products.svg',
                'sizes' => $product['sizes'],
                'colors' => $product['extras'],
                'features' => $product['features'],
            ])->save();

            if ($branchId && DB::getSchemaBuilder()->hasTable('pos_products')) {
                $code = 'JKS-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
                DB::table('pos_products')->updateOrInsert(
                    ['code' => $code],
                    [
                        'branch_id' => $branchId,
                        'website_product_id' => $websiteProduct->id,
                        'name' => $product['name'],
                        'description' => $product['description'],
                        'price' => $product['price'],
                        'stock' => $product['stock'],
                        'image' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }

        if (DB::getSchemaBuilder()->hasTable('home_slides')) {
            foreach ($this->slides($now) as $id => $slide) {
                DB::table('home_slides')->updateOrInsert(['id' => $id], $slide);
            }
        }
    }

    private function addOns($now): array
    {
        return [
            ['name' => 'Carrier Bag', 'description' => 'Reusable shopping bag for checkout.', 'price' => 1.00, 'icon' => 'bi-bag', 'is_active' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gift Wrap', 'description' => 'Simple gift wrapping for selected items.', 'price' => 5.00, 'icon' => 'bi-gift', 'is_active' => true, 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ice Pack', 'description' => 'Cold pack for chilled products.', 'price' => 3.00, 'icon' => 'bi-snow', 'is_active' => true, 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Delivery Crate', 'description' => 'Reusable crate for bulk orders.', 'price' => 10.00, 'icon' => 'bi-box-seam', 'is_active' => true, 'sort_order' => 40, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Warranty Card', 'description' => 'Warranty card for eligible products.', 'price' => 0.00, 'icon' => 'bi-shield-check', 'is_active' => true, 'sort_order' => 50, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Batteries', 'description' => 'Add batteries for compatible products.', 'price' => 8.00, 'icon' => 'bi-battery-charging', 'is_active' => true, 'sort_order' => 60, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cleaning Wipes', 'description' => 'Multipurpose cleaning wipes.', 'price' => 6.00, 'icon' => 'bi-stars', 'is_active' => true, 'sort_order' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Water 500ml', 'description' => 'Bottled water 500ml.', 'price' => 5.00, 'icon' => 'bi-droplet', 'is_active' => true, 'sort_order' => 80, 'created_at' => $now, 'updated_at' => $now],
        ];
    }

    private function products(): array
    {
        return [
            ['name' => 'Premium Rice 5kg', 'category' => 'Groceries', 'description' => 'Long grain rice packed for everyday home cooking.', 'price' => 85.00, 'stock' => 120, 'sizes' => ['Single Bag', '2 Bags', 'Family Pack', 'Bulk Pack'], 'extras' => ['Carrier Bag', 'Delivery Crate', 'Water 500ml'], 'features' => ['Shelf ready', 'Everyday value', 'Pickup or delivery']],
            ['name' => 'Cooking Oil 1L', 'category' => 'Groceries', 'description' => 'Quality cooking oil for home kitchens and small shops.', 'price' => 42.00, 'stock' => 100, 'sizes' => ['Single Bottle', '2 Bottles', 'Carton'], 'extras' => ['Carrier Bag', 'Delivery Crate'], 'features' => ['Kitchen essential', 'Fast checkout', 'Bulk available']],
            ['name' => 'Bottled Water Pack', 'category' => 'Beverages', 'description' => 'Pack of bottled drinking water for home, office, and events.', 'price' => 35.00, 'stock' => 90, 'sizes' => ['6 Pack', '12 Pack', '24 Pack'], 'extras' => ['Carrier Bag', 'Delivery Crate', 'Ice Pack'], 'features' => ['Hydration staple', 'Event friendly', 'Delivery available']],
            ['name' => 'Laundry Detergent 2kg', 'category' => 'Household', 'description' => 'Powder detergent for bright, clean laundry.', 'price' => 58.00, 'stock' => 70, 'sizes' => ['Single Pack', 'Family Pack', 'Wholesale Pack'], 'extras' => ['Carrier Bag', 'Cleaning Wipes', 'Delivery Crate'], 'features' => ['Household essential', 'Great value', 'Bulk available']],
            ['name' => 'Toilet Tissue 10 Pack', 'category' => 'Household', 'description' => 'Soft toilet tissue pack for home and workplace restocking.', 'price' => 48.00, 'stock' => 85, 'sizes' => ['10 Pack', '20 Pack', 'Carton'], 'extras' => ['Carrier Bag', 'Delivery Crate'], 'features' => ['Daily essential', 'Easy restock', 'Bulk available']],
            ['name' => 'Fresh Apples Pack', 'category' => 'Fresh Produce', 'description' => 'Crisp apples selected for snacking, lunch boxes, and home use.', 'price' => 30.00, 'stock' => 55, 'sizes' => ['Small Pack', 'Family Pack', 'Crate'], 'extras' => ['Carrier Bag', 'Gift Wrap', 'Delivery Crate'], 'features' => ['Fresh produce', 'Customer favorite', 'Pickup or delivery']],
            ['name' => 'AA Batteries 4 Pack', 'category' => 'Add-ons', 'description' => 'Reliable batteries for household devices and accessories.', 'price' => 18.00, 'stock' => 100, 'sizes' => ['4 Pack', '8 Pack', 'Counter Display'], 'extras' => ['Carrier Bag', 'Warranty Card'], 'features' => ['Checkout add-on', 'Small electronics ready', 'Easy upsell']],
            ['name' => 'Mixed Grocery Bundle', 'category' => 'Bulk Deals', 'description' => 'A value bundle of everyday pantry and household essentials.', 'price' => 220.00, 'stock' => 25, 'sizes' => ['Starter Bundle', 'Family Bundle', 'Bulk Bundle'], 'extras' => ['Carrier Bag', 'Delivery Crate', 'Cleaning Wipes', 'Water 500ml'], 'features' => ['Everyday value', 'Delivery available', 'Best for restocking']],
        ];
    }

    private function slides($now): array
    {
        return [
            1 => ['main_header' => 'Groceries Ready Daily', 'small_header' => 'Groceries, household essentials, beverages, and fresh produce from ONJECASA.', 'picture' => 'themes/zabaga/assets/images/resources/banner-one-img-1-1.jpg', 'created_at' => $now, 'updated_at' => $now],
            2 => ['main_header' => 'Everyday Essentials In Stock', 'small_header' => 'Shop pantry staples, drinks, personal care, and home supplies.', 'picture' => 'themes/zabaga/assets/images/resources/banner-one-img-1-2.jpg', 'created_at' => $now, 'updated_at' => $now],
            3 => ['main_header' => 'Pickup, Delivery & Bulk Orders', 'small_header' => 'Fresh produce, add-ons, family packs, office restocks, and delivery deals.', 'picture' => 'themes/zabaga/assets/images/resources/banner-one-img-1-3.jpg', 'created_at' => $now, 'updated_at' => $now],
        ];
    }
}


