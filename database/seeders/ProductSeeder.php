<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Classic Cotton Shirt',
                'category' => 'Shirts',
                'brand' => 'GarmentCo',
                'description' => 'Breathable cotton shirts for everyday comfort.',
                'base_price' => 25.00,
                'tax_rate' => 8,
                'colors' => ['Red', 'Blue', 'White'],
                'sizes' => ['S', 'M', 'L', 'XL'],
            ],
            [
                'name' => 'Slim Fit Denim Jeans',
                'category' => 'Jeans',
                'brand' => 'DenimWorks',
                'description' => 'Stretch denim jeans with a flattering silhouette.',
                'base_price' => 45.00,
                'tax_rate' => 8,
                'colors' => ['Indigo', 'Black'],
                'sizes' => ['S', 'M', 'L', 'XL'],
            ],
            [
                'name' => 'Waterproof Windbreaker Jacket',
                'category' => 'Jackets',
                'brand' => 'AllWeather',
                'description' => 'Lightweight jacket designed for rain and wind.',
                'base_price' => 65.00,
                'tax_rate' => 8,
                'colors' => ['Black', 'Olive', 'Navy'],
                'sizes' => ['M', 'L', 'XL', 'XXL'],
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::updateOrCreate(
                ['name' => $productData['name']],
                [
                    'category' => $productData['category'],
                    'brand' => $productData['brand'],
                    'description' => $productData['description'],
                    'base_price' => $productData['base_price'],
                    'tax_rate' => $productData['tax_rate'],
                    'is_active' => true,
                ]
            );

            $product->variations()->delete();

            foreach ($productData['colors'] as $color) {
                foreach ($productData['sizes'] as $size) {
                    $product->variations()->create([
                        'color' => $color,
                        'size' => $size,
                        'sku' => strtoupper(Str::slug($product->name.'-'.$color.'-'.$size)).Str::random(3),
                        'barcode' => (string) random_int(100000000000, 999999999999),
                        'price' => $product->base_price,
                        'stock' => random_int(5, 20),
                    ]);
                }
            }
        }
    }
}
