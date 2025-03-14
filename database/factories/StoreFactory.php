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
            ...fake()->unique()->randomElement([
                ['slug' => 'ah',        'name' => 'Ah'],
                ['slug' => 'vomar',     'name' => 'Vomar'],
                ['slug' => 'jumbo',     'name' => 'Jumbo'],
                ['slug' => 'dirk',      'name' => 'Dirk'],
                ['slug' => 'dekamarkt', 'name' => 'DekaMarkt'],
                ['slug' => 'aldi',      'name' => 'Aldi'],
                ['slug' => 'lidl',      'name' => 'Lidl'],
            ]),
        ];
    }
}
