<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'category' => $this->faker->randomElement(['Shirts', 'Jeans', 'Jackets']),
            'brand' => $this->faker->company(),
            'description' => $this->faker->paragraph(),
            'base_price' => $this->faker->randomFloat(2, 15, 150),
            'tax_rate' => $this->faker->randomElement([5, 10, 15]),
            'is_active' => true,
        ];
    }
}
