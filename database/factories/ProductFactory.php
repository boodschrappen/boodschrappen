<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gtins' => json_encode([fake()->randomNumber(8)]),
            'name' => fake()->words(asText: true),
            'summary' => fake()->sentence(),
            'description' => fake()->paragraph(),
        ];
    }
}
