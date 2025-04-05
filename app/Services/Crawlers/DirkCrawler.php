<?php

namespace App\Services\Crawlers;

use App\Data\DirkProductData;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class DirkCrawler extends Crawler
{
    protected const PAGE_SIZE = 10000;

    protected function getHeaders()
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:102.0) Gecko/20100101 Firefox/102.0'
        ];
    }

    protected function http()
    {
        return Http::retry(3, 1000, throw: false)->withHeaders($this->getHeaders());
    }

    public function fetchCategories(): Collection
    {
        return collect(range('a', 'z'));
    }

    public function fetchProductsByCategory(mixed $query): Collection
    {
        $products = collect();

        $response = $this->http()
            ->get('https://api.dirk.nl/v1/assortmentcache/search/318', [
                'api_key' => '6d3a42a3-6d93-4f98-838d-bcc0ab2307fd',
                'search' => $query,
                'limit' => self::PAGE_SIZE,
            ]);

        if ($response->successful()) {
            $products->push(...$response->json());
        }

        return DirkProductData::factory()
            ->withoutOptionalValues()
            ->collect($products);
    }

    public function fetchDiscounts(): Collection
    {
        return collect();
    }

    public function fetchProduct(mixed $identifier): DirkProductData
    {
        return $this->formatProduct(
            $this->http()
                ->get("https://api.dirk.nl/v1/assortmentcache/318/$identifier", [
                    'api_key' => '6d3a42a3-6d93-4f98-838d-bcc0ab2307fd',
                ])
                ->collect()
        );
    }

    public function formatProduct(mixed $raw): DirkProductData
    {
        return DirkProductData::from($raw);
    }

    public function getStore(): Store
    {
        return Store::firstWhere('slug', 'dirk');
    }
}
