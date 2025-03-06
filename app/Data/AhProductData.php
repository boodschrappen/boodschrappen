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
        public int              $webshopId,
        public int|Optional     $hqId,
        public string           $title,
        public string|Optional  $salesUnitSize,
        public string|Optional  $unitPriceDescription,
        public array|Optional   $images,
        public float|Optional   $currentPrice,
        public float            $priceBeforeBonus,
        public string|Optional  $orderAvailabilityStatus,
        public string|Optional  $mainCategory,
        public string|Optional  $subCategory,
        public string|Optional  $brand,
        public string|Optional  $shopType,
        public bool|Optional    $availableOnline,
        public bool|Optional    $isPreviouslyBought,
        public string           $descriptionHighlights,
        public array|Optional   $propertyIcons,
        public string|Optional  $nutriscore,
        public bool|Optional    $nix18,
        public bool|Optional    $isStapelBonus,
        public array|Optional   $extraDescriptions,
        public bool|Optional    $isBonus,
        public string|Optional  $descriptionFull,
        public bool|Optional    $isOrderable,
        public bool|Optional    $isInfiniteBonus,
        public bool|Optional    $isSample,
        public bool|Optional    $isSponsored,
        public bool|Optional    $isVirtualBundle,
        public array|Optional   $discountLabels,
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
            'reduced_price' => null,
            'original_price' => $this->priceBeforeBonus,
            'raw' => json_encode($this->toArray()),
        ]);
    }
}
