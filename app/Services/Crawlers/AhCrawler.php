<?php

namespace App\Services\Crawlers;

use App\Data\AhProductData;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Spatie\LaravelData\Data;

class AhCrawler extends Crawler
{
    protected readonly int $pageSize;

    public function __construct()
    {
        $this->pageSize = 1000;
    }

    protected function getToken()
    {
        static $token;

        if (!$token) {
            $token = Http::post(
                'https://api.ah.nl/mobile-auth/v1/auth/token/anonymous',
                ['clientId' => 'appie']
            )['access_token'];
        }

        return $token;
    }

    protected function getHeaders()
    {
        return [
            'Host' => 'api.ah.nl',
            'x-dynatrace' => 'MT_3_4_772337796_1_fae7f753-3422-4a18-83c1-b8e8d21caace_0_1589_109',
            'x-application' => 'AHWEBSHOP',
            'user-agent' => 'Appie/8.8.2 Model/phone Android/7.0-API24',
            'Authorization' => 'Bearer ' . $this->getToken(),
        ];
    }

    protected function http()
    {
        return Http::retry(3, 1000, throw: false)->withHeaders($this->getHeaders());
    }

    public function fetchCategories(): Collection
    {
        return $this->http()
            ->get('https://api.ah.nl/mobile-services/v1/product-shelves/categories')
            ->collect();
    }

    public function fetchProductsByCategory(mixed $category): Collection
    {
        $categoryId = $category['id'];

        $products = collect();

        $fetchProductsInCategory = function ($categoryId, $page) use ($products) {
            $response = $this->http()
                ->get('https://api.ah.nl/mobile-services/product/search/v2', [
                    'taxonomyId' => $categoryId,
                    'page' => $page,
                    'size' => $this->pageSize,
                ]);

            if ($response->successful()) {
                $products->push(...$response['products']);
            }

            return $response;
        };

        $initialCategoryRequest = $fetchProductsInCategory($categoryId, 0);

        if ($initialCategoryRequest->failed()) {
            return collect();
        }

        for ($page = 1; $page < $initialCategoryRequest['page']['totalPages']; $page++) {
            Sleep::sleep(5);
            $fetchProductsInCategory($categoryId, $page);
        }

        return AhProductData::factory()
            ->withoutOptionalValues()
            ->collect($products);
    }

    public function fetchDiscounts(): Collection
    {
        return collect();
    }

    public function fetchProduct(mixed $identifier): Data
    {
        return $this->formatProduct(
            $this->http()->get("https://api.ah.nl/mobile-services/product/detail/v4/fir/$identifier")
        );
    }

    public function formatProduct(mixed $raw): Data
    {
        return AhProductData::from($raw);
    }

    public function getStore(): Store
    {
        return Store::firstWhere('slug', 'ah');
    }
}
