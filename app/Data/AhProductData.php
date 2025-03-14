<?php

namespace App\Data;

use App\Contracts\ProductData;
use App\Models\Product;
use App\Models\ProductStore;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class AhProductData extends Data implements ProductData
{
    public function __construct(
        public int                   $webshopId,
        public string                $title,
        public string                $descriptionHighlights,
        public Optional|null|int     $hqId = null,
        public Optional|null|string  $salesUnitSize = null,
        public Optional|null|string  $unitPriceDescription = null,
        public Optional|null|array   $images = null,
        public Optional|null|float   $currentPrice = null,
        public Optional|null|float   $priceBeforeBonus = null,
        public Optional|null|string  $orderAvailabilityStatus = null,
        public Optional|null|string  $mainCategory = null,
        public Optional|null|string  $subCategory = null,
        public Optional|null|string  $brand = null,
        public Optional|null|string  $shopType = null,
        public Optional|null|bool    $availableOnline = null,
        public Optional|null|bool    $isPreviouslyBought = null,
        public Optional|null|array   $propertyIcons = null,
        public Optional|null|string  $nutriscore = null,
        public Optional|null|bool    $nix18 = null,
        public Optional|null|bool    $isStapelBonus = null,
        public Optional|null|array   $extraDescriptions = null,
        public Optional|null|bool    $isBonus = null,
        public Optional|null|string  $descriptionFull = null,
        public Optional|null|bool    $isOrderable = null,
        public Optional|null|bool    $isInfiniteBonus = null,
        public Optional|null|bool    $isSample = null,
        public Optional|null|bool    $isSponsored = null,
        public Optional|null|bool    $isVirtualBundle = null,
        public Optional|null|array   $discountLabels = null,
    ) {}

    public function toProduct(): Product
    {
        return new Product([
            'gtins'       => "[]",
            'name'        => $this->title,
            'summary'     => $this->descriptionHighlights,
            'description' => $this->descriptionHighlights,
        ]);
    }

    public function toStoreProduct(): ProductStore
    {
        return new ProductStore([
            'raw_identifier' => $this->webshopId,
            'reduced_price' => $this->currentPrice,
            'original_price' => $this->priceBeforeBonus ?? $this->currentPrice,
            'raw' => json_encode($this->toArray()),
        ]);
    }

    public static function fromModel(ProductStore $storeProduct): self
    {
        return self::factory()->withOptionalValues()->from([
            'webshopId' => $storeProduct->raw_identifier,
            'title' => $storeProduct->product->name,
            'descriptionHighlights' => $storeProduct->product->summary,
            'currentPrice' => $storeProduct->reduced_price,
            'priceBeforeBonus' => $storeProduct->original_price,
        ]);
    }
}
