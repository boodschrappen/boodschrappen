<?php

namespace App\Services\Crawlers;

use App\Models\Store;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

abstract class Crawler
{
    public function fetchAllProducts(): Collection
    {
        return $this->fetchCategories()->flatMap(
            fn(mixed $category) => $this->fetchProductsByCategory($category)
        );
    }

    abstract function fetchCategories(): Collection;

    abstract function fetchProductsByCategory(mixed $category): Collection;

    abstract function fetchDiscounts(): Collection;

    abstract function fetchProduct(mixed $identifier): Data;

    abstract function formatProduct(mixed $raw): Data;

    abstract function getStore(): Store;
}
