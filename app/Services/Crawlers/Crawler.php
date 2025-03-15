<?php

namespace App\Services\Crawlers;

use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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

    protected function log(string $message, array $context)
    {
        printf($message);
        Log::debug($message, $context);
    }
}
