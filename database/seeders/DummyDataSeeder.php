<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\DiscountTier;
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
        Product::factory(10)
            ->recycle(Store::factory(3)->create())
            ->has(Discount::factory(2))
            ->create();
    }
}
