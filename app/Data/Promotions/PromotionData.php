<?php

namespace App\Data\Promotions;

use Carbon\Carbon;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class PromotionData extends Data
{
    public function __construct(
        public Carbon $start,
        public Carbon $end,
        #[DataCollectionOf(PromotionTierData::class)] public array $tiers
    ) {}
}
