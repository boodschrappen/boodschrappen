<?php

namespace App\Services\Crawlers;

use App\Data\JumboProductData;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class JumboCrawler extends Crawler
{
    protected const PAGE_SIZE = 30;

    protected function getHeaders()
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:148.0) Gecko/20100101 Firefox/148.0',
            'Accept' => 'application/json',
            'Accept-Encoding' => 'gzip',
            'Connection' => 'keep-alive',
        ];
    }

    protected function http()
    {
        return Http::retry(3, 1000, throw: false)->withHeaders($this->getHeaders());
    }

    public function fetchCategories(): Collection
    {
        $response = $this->http()
            ->get('https://mobileapi.jumbo.com/v17/search', [
                'limit' => 1,
            ]);

        if ($response->successful()) {
            $totalItems = $response['products']['total'];
        } else {
            $totalItems = 175000;
        }

        return collect(array_fill(0, ceil($totalItems / self::PAGE_SIZE), null))->map(fn($_, $key) => $key);
    }

    public function fetchProductsByCategory(mixed $chunk): Collection
    {
        $products = collect();

        $response = $this->http()
            ->get('https://mobileapi.jumbo.com/v17/search', [
                'limit' => self::PAGE_SIZE,
                'offset' => self::PAGE_SIZE * $chunk,
            ]);

        if ($response->successful()) {
            $products->push(...$response['products']['data']);
        }

        return JumboProductData::factory()
            ->withoutOptionalValues()
            ->collect($products);
    }

    public function fetchDiscounts(): Collection
    {
        return collect();
    }

    public function fetchProduct(mixed $identifier): JumboProductData
    {
        return $this->formatProduct(
            $this->http()->get("https://mobileapi.jumbo.com/v17/products/$identifier")
                ->collect('product.data')
        );
    }

    public function formatProduct(mixed $raw): JumboProductData
    {
        return JumboProductData::from($raw);
    }

    public function getStore(): Store
    {
        return Store::firstWhere('slug', 'jumbo');
    }
}
