<?php

namespace App\Data\Nutrients;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

/**
 * A collection of food related properties.
 */
class NutritionalData extends Data
{
    public function __construct(
        /** @var array<string> */
        public array $ingredients,
        #[DataCollectionOf(NutrientsData::class)] public array $nutrients,
        #[DataCollectionOf(AllergensData::class)] public array $allergens
    ) {}
}
