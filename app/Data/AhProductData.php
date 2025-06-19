<?php

namespace App\Data;

use App\Contracts\ProductData;
use App\Data\Nutrients\AllergensData;
use App\Data\Nutrients\NutrientsData;
use App\Data\Discounts\DiscountData;
use App\Data\Discounts\DiscountTierData;
use App\Data\Discounts\DiscountUnit;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductStore;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class AhProductData extends Data implements ProductData
{
    public function __construct(
        public int $webshopId,
        public string $title,
        public string $descriptionHighlights,
        public Optional|null|int $hqId,
        public Optional|null|string $salesUnitSize,
        public Optional|null|string $unitPriceDescription,
        public Optional|null|array $images,
        public Optional|null|float $currentPrice,
        public Optional|null|float $priceBeforeBonus,
        public Optional|null|string $orderAvailabilityStatus,
        public Optional|null|string $mainCategory,
        public Optional|null|string $subCategory,
        public Optional|null|string $brand,
        public Optional|null|string $shopType,
        public Optional|null|bool $availableOnline,
        public Optional|null|bool $isPreviouslyBought,
        public Optional|null|array $propertyIcons,
        public Optional|null|string $nutriscore,
        public Optional|null|bool $nix18,
        public Optional|null|bool $isStapelBonus,
        public Optional|null|array $extraDescriptions,
        public Optional|null|bool $isBonus,
        public Optional|null|string $descriptionFull,
        public Optional|null|bool $isOrderable,
        public Optional|null|bool $isInfiniteBonus,
        public Optional|null|bool $isSample,
        public Optional|null|bool $isSponsored,
        public Optional|null|bool $isVirtualBundle,
        public Optional|null|array $discountLabels,
        public Optional|null|string $bonusStartDate,
        public Optional|null|string $bonusEndDate,

        // Only available on details page.
        public Optional|null|int $gln,
        public Optional|null|int $gtin,
        public Optional|null|array $nutritionalInformation,
        public Optional|null|string $foodAndBeverageIngredientStatement,
        public Optional|null|array $allergenInformation
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
            "ingredients" => $this->ingredients(),
            "nutrients" => $this->nutrients(),
            "allergens" => $this->allergens()?->allergens,
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

    public function nutrients(): NutrientsData|null
    {
        if (empty($this->nutritionalInformation)) {
            return null;
        }

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

    public function ingredients(): string|null
    {
        return $this->foodAndBeverageIngredientStatement;
    }

    public function allergens(): AllergensData|null
    {
        if (empty($this->allergenInformation)) {
            return null;
        }

        return new AllergensData(
            array_map(
                fn($allergen) => $allergen["levelOfContainmentCode"]["label"] .
                    " " .
                    $allergen["typeCode"]["label"],
                $this->allergenInformation[0]["items"]
            )
        );
    }

    public function discount(): DiscountData|null
    {
        if (!$this->isBonus) {
            return null;
        }

        return new DiscountData(
            start: Carbon::parse($this->bonusStartDate),
            end: Carbon::parse($this->bonusEndDate),
            tiers: $this->approximateTiers()
        );
    }

    private function approximateTiers(): Collection
    {
        return collect($this->discountLabels ?? [])
            ->map(function ($offer) {
                $defaults = ["description" => $offer["defaultDescription"]];

                switch ($offer["code"]) {
                    case "DISCOUNT_FIXED_PRICE":
                    case "DISCOUNT_AMOUNT":
                    case "DISCOUNT_TIERED_PRICE":
                        return [
                            ...$defaults,
                            "amount" => $offer["price"] ?? $offer["amount"],
                            "unit" => DiscountUnit::Money,
                            "size" => $offer["count"] ?? 1,
                        ];
                    case "DISCOUNT_X_FOR_Y":
                        return [
                            ...$defaults,
                            "amount" => $offer["price"],
                            "unit" => DiscountUnit::Money,
                            "size" => $offer["count"],
                        ];
                    case "DISCOUNT_PERCENTAGE":
                    case "DISCOUNT_TIERED_PERCENT":
                        return [
                            ...$defaults,
                            "amount" => $offer["percentage"],
                            "unit" => DiscountUnit::Percentage,
                            "size" => $offer["count"] ?? 1,
                        ];
                    case "DISCOUNT_ONE_HALF_PRICE":
                        return [
                            ...$defaults,
                            "amount" => 25,
                            "unit" => DiscountUnit::Percentage,
                            "size" => 2,
                        ];
                    case "DISCOUNT_X_PLUS_Y_FREE":
                        $size = $offer["count"] + $offer["freeCount"];
                        return [
                            ...$defaults,
                            "amount" => ($offer["freeCount"] / $size) * 100,
                            "unit" => DiscountUnit::Percentage,
                            "size" => $size,
                        ];
                    case "DISCOUNT_BUNDLE_BULK":
                        return [
                            ...$defaults,
                            "amount" => $offer["percentage"],
                            "unit" => DiscountUnit::Percentage,
                            "size" => 1,
                        ];
                    case "DISCOUNT_WEIGHT":
                        return [
                            ...$defaults,
                            "amount" => $offer["price"],
                            "unit" => DiscountUnit::Money,
                            "size" => 100,
                            // TODO: Add a size unit to support weighted discounts.
                        ];
                    default:
                        return null;
                }
            })
            ->filter(fn($t) => $t !== null)
            ->map(fn($t) => DiscountTierData::from($t));
    }
}
