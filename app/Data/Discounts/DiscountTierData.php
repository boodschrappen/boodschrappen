<?php

namespace App\Data\Discounts;

use Spatie\LaravelData\Data;

class DiscountTierData extends Data
{
    public function __construct(
        public string $description,
        public float|int $amount,
        public DiscountUnit $unit,
        public int $size
    ) {}
}
