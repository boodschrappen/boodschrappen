<?php

namespace App\Data\Discounts;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class DiscountData extends Data
{
    public function __construct(
        public Carbon $start,
        public Carbon $end,
        #[DataCollectionOf(DiscountTierData::class)] public array $tiers
    ) {}
}
