<?php

namespace App\Services\Crawlers;

use App\Contracts\ProductData;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

abstract class Crawler
{
    /** @return Collection|\App\Data\ProductData[] */
    public function fetchAllProducts(): Collection
    {
        return $this->fetchCategories()->flatMap(
            fn(mixed $category) => $this->fetchProductsByCategory($category)
        );
    }

    abstract function fetchCategories(): Collection;

    /** @return Collection|\App\Data\ProductData[] */
    abstract function fetchProductsByCategory(mixed $category): Collection;

    /** @return Collection|\App\Data\Discounts\DiscountData[] */
    abstract function fetchDiscounts(): Collection;

    abstract function fetchProduct(mixed $identifier): ProductData;

    abstract function formatProduct(mixed $raw): ProductData;

    abstract function getStore(): Store;

    protected function log(string $message, array $context)
    {
        printf($message);
        Log::debug($message, $context);
    }
}
