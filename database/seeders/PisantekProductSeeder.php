<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Str;

class PisantekProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get required references
        $pisantek = Business::where('name_business', 'Pisantek')->first();
        $snackCategory = ProductCategory::where('name_category_product', 'Snack & Camilan')->first();
        $traditionalCategory = ProductCategory::where('name_category_product', 'Produk Tradisional')->first();
        $availableStatus = ProductStatus::where('product_status', 'Available')->first();
        $limitedStatus = ProductStatus::where('product_status', 'Limited Stock')->first();
        $sulyadee = User::where('email', 'sulyadee@pioneer.com')->first();

        if (!$pisantek || !$snackCategory || !$traditionalCategory || !$availableStatus || !$limitedStatus || !$sulyadee) {
            $this->command->error('Required data not found. Please run other seeders first.');
            return;
        }

        $products = [
            [
                'name_product' => 'Pisang Cokelat Original',
                'description' => 'Pisang segar dibalut cokelat premium dengan topping kelapa parut. Camilan favorit yang manis dan lezat.',
                'price_product' => 15000.00,
                'stock' => 50,
                'id_business' => $pisantek->id,
                'id_product_category' => $snackCategory->id,
                'id_product_status' => $availableStatus->id,
            ],
            [
                'name_product' => 'Pisang Keju Spesial',
                'description' => 'Pisang manis dengan taburan keju cheddar yang gurih. Perpaduan rasa manis dan asin yang sempurna.',
                'price_product' => 18000.00,
                'stock' => 35,
                'id_business' => $pisantek->id,
                'id_product_category' => $snackCategory->id,
                'id_product_status' => $availableStatus->id,
            ],
            [
                'name_product' => 'Pisang Cokelat Kacang',
                'description' => 'Pisang cokelat dengan topping kacang tanah sangrai yang renyah. Tambahan protein untuk camilan sehat.',
                'price_product' => 17000.00,
                'stock' => 40,
                'id_business' => $pisantek->id,
                'id_product_category' => $snackCategory->id,
                'id_product_status' => $availableStatus->id,
            ],
            [
                'name_product' => 'Pisang Keju Cokelat Combo',
                'description' => 'Kombinasi terbaik pisang dengan keju dan cokelat. Triple sensation dalam satu gigitan!',
                'price_product' => 22000.00,
                'stock' => 25,
                'id_business' => $pisantek->id,
                'id_product_category' => $snackCategory->id,
                'id_product_status' => $availableStatus->id,
            ],
            [
                'name_product' => 'Pisang Bakar Madu',
                'description' => 'Pisang bakar tradisional dengan madu asli dan margarin. Hangat dan manis untuk segala cuaca.',
                'price_product' => 12000.00,
                'stock' => 30,
                'id_business' => $pisantek->id,
                'id_product_category' => $traditionalCategory->id,
                'id_product_status' => $availableStatus->id,
            ],
            [
                'name_product' => 'Pisang Nugget Mini',
                'description' => 'Pisang nugget kecil-kecil dengan berbagai topping pilihan. Cocok untuk camilan anak-anak.',
                'price_product' => 20000.00,
                'stock' => 15,
                'id_business' => $pisantek->id,
                'id_product_category' => $snackCategory->id,
                'id_product_status' => $limitedStatus->id,
            ],
            [
                'name_product' => 'Pisang Crispy Original',
                'description' => 'Pisang goreng tepung crispy dengan tekstur renyah di luar dan lembut di dalam.',
                'price_product' => 13000.00,
                'stock' => 45,
                'id_business' => $pisantek->id,
                'id_product_category' => $traditionalCategory->id,
                'id_product_status' => $availableStatus->id,
            ],
            [
                'name_product' => 'Pisang Cokelat Premium',
                'description' => 'Pisang dengan cokelat import berkualitas tinggi dan hiasan almond slice. Kemewahan rasa pisang.',
                'price_product' => 25000.00,
                'stock' => 20,
                'id_business' => $pisantek->id,
                'id_product_category' => $snackCategory->id,
                'id_product_status' => $availableStatus->id,
            ]
        ];

        foreach ($products as $productData) {
            $product = Product::firstOrCreate(
                [
                    'name_product' => $productData['name_product'],
                    'id_business' => $productData['id_business']
                ],
                array_merge($productData, [
                    'uuid' => Str::uuid(),
                    'created_by' => $sulyadee->id,
                    'updated_by' => $sulyadee->id,
                ])
            );
        }

        $this->command->info('Pisantek products seeded successfully!');
        $this->command->info('Created 8 delicious banana-based products for Pisantek business.');
    }
}
