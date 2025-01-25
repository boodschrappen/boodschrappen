<?php

namespace Database\Factories;

use App\Models\DiscountTier;
use App\Models\ProductStore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Discount>
 */
class DiscountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'start' => now(),
            'end' => now()->addWeek(),
            'product_store_id' => ProductStore::factory()
        ];
    }

    public function configure()
    {
        return $this->has(DiscountTier::factory(2), 'tiers');
    }
}
