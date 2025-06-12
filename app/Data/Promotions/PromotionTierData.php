<?php

namespace App\Data\Promotions;

use Spatie\LaravelData\Data;

class PromotionTierData extends Data
{
    public function __construct(
        public string $description,
        public float|int $amount,
        public PromotionUnit $unit,
        public int $size
    ) {}
}
