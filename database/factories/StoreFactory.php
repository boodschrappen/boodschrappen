<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Ah',
                'Vomar',
                'Jumbo',
                'Dirk',
                'DekaMarkt',
                'Aldi',
                'Lidl',
            ]),
            'slug' => fake()->randomElement([
                'ah',
                'vomar',
                'jumbo',
                'dirk',
                'dekamarkt',
                'aldi',
                'lidl',
            ])
        ];
    }
}
