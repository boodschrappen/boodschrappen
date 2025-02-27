<?php

namespace App\Data;

use Spatie\LaravelData\Data;

abstract class ProductData extends Data
{
    public function __construct(
        public int $gtin,
        public int $rawIdentifier,
    ) {}

    public static function fromRaw($rawData): self
    {
        return self::from($rawData);
    }
}
