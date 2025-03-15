<?php

namespace App\Services\Crawlers;

use App\Data\VomarProductData;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Data;

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
        $products =  $this->http()
            ->get('https://api.vomar.nl/api/v1/article/getAllArticlesForMainGroup', [
                'departmentNumber' => $category['departmentNumber'],
                'mainGroupNumber' => $category['mainGroupNumber'],
            ])
            ->collect();

        return VomarProductData::factory()
            ->withoutOptionalValues()
            ->collect($products);
    }

    public function fetchDiscounts(): Collection
    {
        return collect();
    }

    public function fetchProduct(mixed $identifier): Data
    {
        return $this->formatProduct([]);
    }

    public function formatProduct(mixed $raw): Data
    {
        return VomarProductData::from($raw);
    }

    public function getStore(): Store
    {
        return Store::firstWhere('slug', 'vomar');
    }
}
