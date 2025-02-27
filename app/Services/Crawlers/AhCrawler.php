<?php

namespace App\Services\Crawlers;

use App\Data\AhProductData;
use App\Data\ProductData;
use App\Models\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;

class AhCrawler extends Crawler
{
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
        return Http::withHeaders($this->getHeaders());
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

        return Cache::remember(AhCrawler::class . '_category_products_' . $categoryId, 3600 * 24 * 7, function () use ($categoryId) {
            $fetchProductsInCategory = fn($categoryId, $page) => $this->http()
                ->get('https://api.ah.nl/mobile-services/product/search/v2', [
                    'taxonomyId' => $categoryId,
                    'page' => $page,
                    'size' => 1000,
                ]);

            $initialCategoryRequest = $fetchProductsInCategory($categoryId, 0);
            $products = collect($initialCategoryRequest['products']);

            for ($page = 1; $page < $initialCategoryRequest['page']['totalPages']; $page++) {
                Sleep::sleep(5);
                $products->merge($fetchProductsInCategory($categoryId, $page));
            }

            return $products;
        });
    }

    public function fetchDiscounts(): Collection
    {
        return collect();
    }

    public function fetchProduct(mixed $identifier): ProductData
    {
        return Cache::remember(self::class, 3600 * 24, fn() => $this->formatProduct(
            $this->http()->get("https://api.ah.nl/mobile-services/product/detail/v4/fir/$identifier")
        ));
    }

    public function formatProduct(mixed $raw): ProductData
    {
        return AhProductData::fromRaw($raw);
    }

    public function getStore(): Store
    {
        return Store::firstWhere('slug', 'ah');
    }
}
