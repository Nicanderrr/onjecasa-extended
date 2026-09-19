<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ProductPage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'ONJECASA Superadmin',
                'password' => bcrypt('password'),
                'is_admin' => 2,
                'role' => 'superadmin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'ONJECASA Admin',
                'password' => bcrypt('password'),
                'is_admin' => 1,
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $branchAdmin = User::firstOrCreate(
            ['email' => 'branchadmin@onjecasa.test'],
            [
                'name' => 'ONJECASA Branch Admin',
                'password' => bcrypt('password'),
                'is_admin' => 0,
                'role' => 'branch_admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $cashier = User::firstOrCreate(
            ['email' => 'cashier@gmail.com'],
            [
                'name' => 'ONJECASA Cashier',
                'password' => bcrypt('password'),
                'is_admin' => 0,
                'role' => 'cashier',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $branchId = DB::table('branches')->where('code', 'ALHAJI')->value('id');
        if (! $branchId) {
            $branchId = DB::table('branches')->insertGetId([
                'name' => 'ONJECASA - Alhaji',
                'code' => 'ALHAJI',
                'phone' => '+233578339542',
                'address' => 'Nii Okaiman West Main Road, Greater Accra',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (DB::getSchemaBuilder()->hasTable('branch_user')) {
            foreach ([$superadmin->id, $admin->id, $branchAdmin->id, $cashier->id] as $userId) {
                DB::table('branch_user')->updateOrInsert(
                    ['branch_id' => $branchId, 'user_id' => $userId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        foreach ([
            'Groceries',
            'Beverages',
            'Household',
            'Personal Care',
            'Add-ons',
            'Fresh Produce',
            'Bulk Deals',
        ] as $category) {
            Category::firstOrCreate(['name' => $category]);

            if (DB::getSchemaBuilder()->hasTable('pos_categories')) {
                DB::table('pos_categories')->updateOrInsert(
                    ['branch_id' => $branchId, 'name' => $category],
                    ['code' => $this->posCategoryCode($category, (int) $branchId), 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        foreach ([
            [
                'Premium Rice 5kg',
                'Groceries',
                'Long grain rice packed for everyday home cooking.',
                85.00,
                120,
                ['Single Bag', '2 Bags', 'Family Pack', 'Bulk Pack'],
                ['Carrier Bag', 'Delivery Crate', 'Water 500ml'],
                ['Shelf ready', 'Everyday value', 'Pickup or delivery'],
            ],
            [
                'Cooking Oil 1L',
                'Groceries',
                'Quality cooking oil for home kitchens and small shops.',
                42.00,
                100,
                ['Single Bottle', '2 Bottles', 'Carton'],
                ['Carrier Bag', 'Delivery Crate'],
                ['Kitchen essential', 'Fast checkout', 'Bulk available'],
            ],
            [
                'Bottled Water Pack',
                'Beverages',
                'Pack of bottled drinking water for home, office, and events.',
                35.00,
                90,
                ['6 Pack', '12 Pack', '24 Pack'],
                ['Carrier Bag', 'Delivery Crate', 'Ice Pack'],
                ['Hydration staple', 'Event friendly', 'Delivery available'],
            ],
            [
                'Laundry Detergent 2kg',
                'Household',
                'Powder detergent for bright, clean laundry.',
                58.00,
                70,
                ['Single Pack', 'Family Pack', 'Wholesale Pack'],
                ['Carrier Bag', 'Cleaning Wipes', 'Delivery Crate'],
                ['Household essential', 'Great value', 'Bulk available'],
            ],
            [
                'Toilet Tissue 10 Pack',
                'Household',
                'Soft toilet tissue pack for home and workplace restocking.',
                48.00,
                85,
                ['10 Pack', '20 Pack', 'Carton'],
                ['Carrier Bag', 'Delivery Crate'],
                ['Daily essential', 'Easy restock', 'Bulk available'],
            ],
            [
                'Fresh Apples Pack',
                'Fresh Produce',
                'Crisp apples selected for snacking, lunch boxes, and home use.',
                30.00,
                55,
                ['Small Pack', 'Family Pack', 'Crate'],
                ['Carrier Bag', 'Gift Wrap', 'Delivery Crate'],
                ['Fresh produce', 'Customer favorite', 'Pickup or delivery'],
            ],
            [
                'AA Batteries 4 Pack',
                'Add-ons',
                'Reliable batteries for household devices and accessories.',
                18.00,
                100,
                ['4 Pack', '8 Pack', 'Counter Display'],
                ['Carrier Bag', 'Warranty Card'],
                ['Checkout add-on', 'Small electronics ready', 'Easy upsell'],
            ],
            [
                'Mixed Grocery Bundle',
                'Bulk Deals',
                'A value bundle of everyday pantry and household essentials.',
                220.00,
                25,
                ['Starter Bundle', 'Family Bundle', 'Bulk Bundle'],
                ['Carrier Bag', 'Delivery Crate', 'Cleaning Wipes', 'Water 500ml'],
                ['Everyday value', 'Delivery available', 'Best for restocking'],
            ],
        ] as $index => $product) {
            $websiteProduct = ProductPage::updateOrCreate(
                ['name' => $product[0]],
                [
                    'description' => $product[1],
                    'contents' => $product[2],
                    'price' => $product[3],
                    'stock' => $product[4],
                    'picture' => 'images/onjecasa-products.svg',
                    'sizes' => $product[5],
                    'colors' => $product[6],
                    'features' => $product[7],
                ]
            );

            if (DB::getSchemaBuilder()->hasTable('pos_products')) {
                $posPayload = [
                    'branch_id' => $branchId,
                    'website_product_id' => $websiteProduct->id,
                    'code' => 'JKS-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'name' => $product[0],
                    'description' => $product[2],
                    'price' => $product[3],
                    'stock' => $product[4],
                    'image' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ];

                $posProductId = DB::table('pos_products')
                    ->where('branch_id', $branchId)
                    ->where('code', $posPayload['code'])
                    ->value('id');
                if ($posProductId) {
                    DB::table('pos_products')->where('id', $posProductId)->update($posPayload);
                } else {
                    $posProductId = DB::table('pos_products')->insertGetId($posPayload);
                }

                $websiteProduct->forceFill(['pos_product_id' => $posProductId])->save();
            }
        }

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
                    ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        if (DB::getSchemaBuilder()->hasTable('home_slides')) {
            foreach ([
                1 => [
                    'main_header' => 'Groceries Ready Daily',
                    'small_header' => 'Groceries, household essentials, beverages, and fresh produce from ONJECASA.',
                    'picture' => 'themes/zabaga/assets/images/resources/banner-one-img-1-1.jpg',
                ],
                2 => [
                    'main_header' => 'Everyday Essentials In Stock',
                    'small_header' => 'Shop pantry staples, drinks, personal care, and home supplies.',
                    'picture' => 'themes/zabaga/assets/images/resources/banner-one-img-1-2.jpg',
                ],
                3 => [
                    'main_header' => 'Pickup, Delivery & Bulk Orders',
                    'small_header' => 'Fresh produce, add-ons, family packs, office restocks, and delivery deals.',
                    'picture' => 'themes/zabaga/assets/images/resources/banner-one-img-1-3.jpg',
                ],
                4 => [
                    'main_header' => 'About Us Image One',
                    'small_header' => 'Large photo beside the ONJECASA homepage section.',
                    'picture' => 'themes/zabaga/assets/images/resources/about-three-img-1.jpg',
                ],
                5 => [
                    'main_header' => 'About Us Image Two',
                    'small_header' => 'Small overlay photo beside the ONJECASA homepage section.',
                    'picture' => 'themes/zabaga/assets/images/resources/about-three-img-2.jpg',
                ],
                6 => [
                    'main_header' => 'Order Mode Image One',
                    'small_header' => 'Photo for pickup orders in the shopping modes homepage section.',
                    'picture' => 'themes/zabaga/assets/images/resources/event-1-1.jpg',
                ],
                7 => [
                    'main_header' => 'Order Mode Image Two',
                    'small_header' => 'Photo for delivery orders in the shopping modes homepage section.',
                    'picture' => 'themes/zabaga/assets/images/resources/event-1-2.jpg',
                ],
                8 => [
                    'main_header' => 'Order Mode Image Three',
                    'small_header' => 'Photo for office restocks in the shopping modes homepage section.',
                    'picture' => 'themes/zabaga/assets/images/resources/event-1-3.jpg',
                ],
                9 => [
                    'main_header' => 'Order Mode Image Four',
                    'small_header' => 'Photo for family packs in the shopping modes homepage section.',
                    'picture' => 'themes/zabaga/assets/images/resources/event-1-4.jpg',
                ],
                10 => [
                    'main_header' => 'Order Mode Image Five',
                    'small_header' => 'Photo for bulk and event supply orders in the shopping modes homepage section.',
                    'picture' => 'themes/zabaga/assets/images/resources/event-1-5.jpg',
                ],
                11 => [
                    'main_header' => 'Store Favorites Background',
                    'small_header' => 'Background image behind ONJECASA store favorites: everyday products ready for pickup and delivery.',
                    'picture' => 'themes/zabaga/assets/images/backgrounds/causes-three-bg.jpg',
                ],
            ] as $id => $slide) {
                DB::table('home_slides')->updateOrInsert(
                    ['id' => $id],
                    $slide + ['updated_at' => now(), 'created_at' => now()]
                );
            }

            foreach ([
                'menu_favorites_overlay_strength' => '90',
                'menu_favorites_overlay_color' => '#111111',
            ] as $key => $value) {
                DB::table('pos_settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }
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
}


