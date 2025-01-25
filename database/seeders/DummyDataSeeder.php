<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stores = Store::factory(2)->create();

        Product::factory(10)
            ->hasAttached(
                $stores,
                ['original_price' => fake()->randomFloat(), 'raw' => json_encode([])]
            )
            ->create();
    }
}
