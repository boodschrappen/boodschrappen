<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductStore>
 */
class ProductStoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'original_price' => fake()->randomFloat(),
            'reduced_price' => fake()->randomFloat(),
            'raw' => [],
            'raw_identifier' => fake()->randomDigit(),
            'product_id' => Product::factory(),
            'store_id' => Store::factory(),
        ];
    }
}
