<?php

namespace App\Services\Crawlers;

use App\Data\VomarProductData;
use App\Data\ProductData;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class VomarCrawler extends Crawler
{
    protected function getHeaders()
    {
        return [];
    }

    protected function http()
    {
        return Http::withHeaders($this->getHeaders());
    }

    public function fetchCategories(): Collection
    {
        return collect(json_decode(file_get_contents(__DIR__ . '/vomar_departments.json'), true));
    }

    public function fetchProductsByCategory(mixed $category): Collection
    {
        $cacheKey = VomarCrawler::class . '_category_products_' . $category['departmentNumber'] . '_' . $category['mainGroupNumber'];

        return Cache::remember($cacheKey, 3600 * 24 * 7, function () use ($category) {
            return $this->http()->get('https://api.vomar.nl/api/v1/article/getAllArticlesForMainGroup', [
                'departmentNumber' => $category['departmentNumber'],
                'mainGroupNumber' => $category['mainGroupNumber'],
            ])->collect();
        });
    }

    public function fetchDiscounts(): Collection
    {
        return collect();
    }

    public function fetchProduct(mixed $identifier): ProductData
    {
        return new VomarProductData($identifier);
    }

    public function formatProduct(mixed $raw): ProductData
    {
        return VomarProductData::fromRaw($raw);
    }

    public function getStore(): Store
    {
        return Store::firstWhere('slug', 'vomar');
    }
}
