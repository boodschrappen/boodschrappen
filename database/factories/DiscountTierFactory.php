<?php

namespace Database\Factories;

use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DiscountTier>
 */
class DiscountTierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'description' => fake()->paragraph(),
            'amount' => fake()->randomFloat(),
            'unit' => fake()->randomElement(['money', 'percentage']),
            'size' => fake()->randomNumber(),
            'discount_id' => Discount::factory(),
        ];
    }
}
