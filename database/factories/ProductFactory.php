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
            'gtins' => [fake()->randomNumber(8)],
            'title' => fake()->title(),
            'summary' => fake()->words(9),
            'description' => fake()->paragraph(),
        ];
    }
}
