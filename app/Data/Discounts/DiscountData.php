<?php

namespace App\Data\Discounts;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class DiscountData extends Data
{
    public function __construct(
        public Carbon $start,
        public Carbon $end,

        /** @var Collection<DiscountTierData> */
        public Collection $tiers
    ) {}
}
