<?php

namespace App\Data\Nutrients;

use Spatie\LaravelData\Data;

class AllergensData extends Data
{
    public function __construct(public array $allergens) {}
}
