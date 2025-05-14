<?php

namespace App\Data\Nutrients;

use Spatie\LaravelData\Data;

class NutrientsData extends Data
{
    public function __construct(public array $headings, public array $rows) {}
}
