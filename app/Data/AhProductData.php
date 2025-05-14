<?php

namespace App\Data;

use App\Contracts\ProductData;
use App\Data\Nutrients\AllergensData;
use App\Data\Nutrients\NutrientsData;
use App\Models\Product;
use App\Models\ProductStore;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class AhProductData extends Data implements ProductData
{
    public function __construct(
        public int $webshopId,
        public string $title,
        public string $descriptionHighlights,
        public Optional|null|int $hqId = null,
        public Optional|null|string $salesUnitSize = null,
        public Optional|null|string $unitPriceDescription = null,
        public Optional|null|array $images = null,
        public Optional|null|float $currentPrice = null,
        public Optional|null|float $priceBeforeBonus = null,
        public Optional|null|string $orderAvailabilityStatus = null,
        public Optional|null|string $mainCategory = null,
        public Optional|null|string $subCategory = null,
        public Optional|null|string $brand = null,
        public Optional|null|string $shopType = null,
        public Optional|null|bool $availableOnline = null,
        public Optional|null|bool $isPreviouslyBought = null,
        public Optional|null|array $propertyIcons = null,
        public Optional|null|string $nutriscore = null,
        public Optional|null|bool $nix18 = null,
        public Optional|null|bool $isStapelBonus = null,
        public Optional|null|array $extraDescriptions = null,
        public Optional|null|bool $isBonus = null,
        public Optional|null|string $descriptionFull = null,
        public Optional|null|bool $isOrderable = null,
        public Optional|null|bool $isInfiniteBonus = null,
        public Optional|null|bool $isSample = null,
        public Optional|null|bool $isSponsored = null,
        public Optional|null|bool $isVirtualBundle = null,
        public Optional|null|array $discountLabels = null,

        // Only available on details page.
        public Optional|null|int $gln = null,
        public Optional|null|int $gtin = null,
        public Optional|null|array $nutritionalInformation = null,
        public Optional|null|string $foodAndBeverageIngredientStatement = null,
        public Optional|null|array $allergenInformation = null
    ) {}

    public static function fromModel(ProductStore $storeProduct): self
    {
        return self::factory()
            ->withoutOptionalValues()
            ->from([
                "webshopId" => $storeProduct->raw_identifier,
                "title" => $storeProduct->product->name,
                "descriptionHighlights" => $storeProduct->product->summary,
                "currentPrice" => $storeProduct->reduced_price,
                "priceBeforeBonus" => $storeProduct->original_price,
            ]);
    }

    public function toProduct(): Product
    {
        return new Product([
            "gtins" => $this->gtin ? [$this->gtin] : [],
            "name" => $this->title,
            "summary" => $this->descriptionHighlights,
            "description" => $this->descriptionHighlights,
            "image" => $this->images ? $this->images[0]["url"] : null,
        ]);
    }

    public function toStoreProduct(): ProductStore
    {
        return new ProductStore([
            "raw_identifier" => $this->webshopId,
            "reduced_price" => $this->currentPrice,
            "original_price" => $this->priceBeforeBonus ?? $this->currentPrice,
            "raw" => $this->toArray(),
        ]);
    }

    public function nutrients(): NutrientsData
    {
        return NutrientsData::from([
            "headings" => [
                "",
                ...array_map(
                    fn($header) => implode(" ", [
                        "Per",
                        $header["nutrientBasisQuantity"]["value"],
                        $header["nutrientBasisQuantity"]["measurementUnitCode"][
                            "label"
                        ],
                    ]),
                    $this->nutritionalInformation["nutrientHeaders"]
                ),
            ],
            "rows" => Arr::flatten(
                depth: 1,
                array: array_map(
                    fn($header) => array_map(function ($detail) {
                        $quantity = $detail["quantityContained"][0];

                        return [
                            $detail["nutrientTypeCode"]["label"],
                            $quantity["value"] .
                            " " .
                            $quantity["measurementUnitCode"]["value"],
                        ];
                    }, $header["nutrientDetail"]),
                    $this->nutritionalInformation["nutrientHeaders"]
                )
            ),
        ]);
    }

    /** @return Array<string> */
    public function ingredients(): array
    {
        return [$this->foodAndBeverageIngredientStatement];
    }

    public function allergens(): AllergensData
    {
        return new AllergensData(
            array_map(
                fn($allergen) => $allergen["levelOfContainmentCode"]["label"] .
                    " " .
                    $allergen["typeCode"]["label"],
                $this->allergenInformation[0]["items"]
            )
        );
    }
}
