<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = Store::all();

        for ($i = 0; $i < 10; $i++) {
            Product::factory()
                ->hasAttached(
                    $stores->random(),
                    [
                        'original_price' => fake()->randomFloat(),
                        'raw_identifier' => random_int(0, 10000),
                        'raw' => json_encode([])
                    ]
                )
                ->create();
        }
    }
}
