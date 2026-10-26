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
            'name' => fake()->words(3, true),
            'price' => number_format(fake()->randomFloat(2, 0, 10000), 2, '.', ''),
            'category' => fake()->word(),
            'quantity' => fake()->numberBetween(0, 1000),
        ];
    }
}
