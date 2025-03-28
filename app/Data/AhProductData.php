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

        // Only available on details page.
        public Optional|null|int     $gln = null,
        public Optional|null|int     $gtin = null,
        public Optional|null|array   $nutritionalInformation = null,
        public Optional|null|string  $foodAndBeverageIngredientStatement = null,
        public Optional|null|array   $allergenInformation = null,
    ) {}

    public function toProduct(): Product
    {
        return new Product([
            'gtins'       => $this->gtin ? [$this->gtin] : [],
            'name'        => $this->title,
            'summary'     => $this->descriptionHighlights,
            'description' => $this->descriptionHighlights,
            'image'       => $this->images ? $this->images[0]['url'] : null,
            'nutrition'   => $this->getNutritionalData(),
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
        return self::factory()->withoutOptionalValues()->from([
            'webshopId' => $storeProduct->raw_identifier,
            'title' => $storeProduct->product->name,
            'descriptionHighlights' => $storeProduct->product->summary,
            'currentPrice' => $storeProduct->reduced_price,
            'priceBeforeBonus' => $storeProduct->original_price,
        ]);
    }

    protected function getNutritionalData(): ?array
    {
        $info = $this->nutritionalInformation;

        if (empty($info)) {
            return null;
        }

        $headers = $info['nutrientHeaders'];
        $rows = [];

        foreach ($headers as $header) {
            $details = $header['nutrientDetail'];

            foreach ($details as $detail) {
                $quantity = $detail['quantityContained'][0];

                if (isset($rows[$detail['nutrientTypeCode']['label']])) {
                    $rows[$detail['nutrientTypeCode']['label']][1] .= ' (' . $quantity['value'] . ' ' . $quantity['measurementUnitCode']['value'] . ')';
                } else {
                    $rows[$detail['nutrientTypeCode']['label']] = [
                        $detail['nutrientTypeCode']['label'],
                        $quantity['value'] . ' ' . $quantity['measurementUnitCode']['value'],
                    ];
                }
            }
        }

        return $rows;
    }
}
