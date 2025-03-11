<?php

namespace App\Contracts;

use App\Models\Product;
use App\Models\ProductStore;
use Spatie\LaravelData\Data;

interface ProductData
{
    public function toProduct(): Product;

    public function toStoreProduct(): ProductStore;

    public static function fromModel(ProductStore $storeProduct): Data;
}
