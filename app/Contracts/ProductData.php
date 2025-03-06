<?php

namespace App\Contracts;

use App\Models\Product;
use App\Models\ProductStore;

interface ProductData
{
    public function toProduct(): Product;

    public function toStoreProduct(): ProductStore;
}
