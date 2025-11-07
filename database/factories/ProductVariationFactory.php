<?php

namespace Database\Factories;

use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductVariationFactory extends Factory
{
    protected $model = ProductVariation::class;

    public function definition(): array
    {
        return [
            'color' => $this->faker->randomElement(['Red', 'Blue', 'Black', 'White']),
            'size' => $this->faker->randomElement(['S', 'M', 'L', 'XL', 'XXL']),
            'sku' => Str::upper(Str::random(8)),
            'barcode' => Str::random(12),
            'price' => $this->faker->randomFloat(2, 15, 150),
            'stock' => $this->faker->numberBetween(5, 30),
        ];
    }
}
