<?php

namespace App\Data;

use App\Contracts\ProductData;
use App\Data\Nutrients\AllergensData;
use App\Data\Nutrients\NutrientsData;
use App\Data\Discounts\DiscountData;
use App\Data\Discounts\DiscountTierData;
use App\Data\Discounts\DiscountUnit;
use App\Models\Product;
use App\Models\ProductStore;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class DirkProductData extends Data implements ProductData
{
    public function __construct(
        public int $ProductID,
        public string $MainDescription,
        public array $ProductPrices,
        public Optional|null|string $ProductNumber,
        public Optional|null|string $SubDescription = null,
        public Optional|null|string $CommercialContent = null,
        public Optional|null|string $MaxPerCustomer = null,
        public Optional|null|string $DepositMoney = null,
        public Optional|null|string $Brand = null,
        public Optional|null|bool $ProductOnline = null,
        public Optional|null|bool $ProductInStock = null,
        public Optional|null|bool $WeightArticle = null,
        public Optional|null|bool $ScaleIndicator = null,
        public Optional|null|string $PiecesInWeight = null,
        public Optional|null|string $AlcoholPercentage = null,
        public Optional|null|bool $TemporaryNotAvailable = null,
        public Optional|null|string $PublicationAfter = null,
        public Optional|null|string $WeightOfPeicesInWeight = null,
        public Optional|null|array $ProductPicture = null,
        public Optional|null|array $ProductPictures = null,
        public Optional|null|array $Logos = null,
        public Optional|null|array $WebSubGroups = null,
        public Optional|null|array $ProductOffers = null,
        public Optional|null|array $StoreAssortments = null,
        public Optional|null|bool $IsSingleUsePlastic = null,

        // Only available on details page.
        public Optional|null|array $Nutrition = null,
        public Optional|null|array $ProductDeclarations = null,
        public Optional|null|array $ProductBarcodes = null
    ) {}

    public static function fromModel(ProductStore $storeProduct): self
    {
        return self::from([
            "id" => $storeProduct->raw_identifier,
            "MainDescription" => $storeProduct->product->name,
            "ProductPrices" => [
                "RegularPrice" => $storeProduct->original_price,
            ],
        ]);
    }

    public function toProduct(): Product
    {
        return new Product([
            "gtins" => $this->ProductBarcodes
                ? [$this->ProductBarcodes[0]["Barcode"]]
                : [],
            "name" => $this->Brand . " " . $this->MainDescription,
            "summary" => $this->SubDescription ?? "",
            "description" => "",
            "image" => $this->ProductPictures
                ? $this->ProductPictures[0]["Url"]
                : $this->ProductPicture["Url"] ?? null,
            "ingredients" => $this->ingredients(),
            "nutrients" => $this->nutrients(),
            "allergens" => $this->allergens()?->allergens,
        ]);
    }

    public function toStoreProduct(): ProductStore
    {
        return new ProductStore([
            "raw_identifier" => $this->ProductID,
            "reduced_price" => $this->ProductPrices[0]["Price"],
            "original_price" => $this->ProductPrices[0]["RegularPrice"],
            "raw" => $this->toArray(),
        ]);
    }

    public function nutrients(): NutrientsData|null
    {
        if (
            empty($this->ProductDeclarations) ||
            empty($this->ProductDeclarations[0]["NutritionInformation"])
        ) {
            return null;
        }

        $rowFn = fn($row) => [$row["Text"], $row["ValueAsSold"]];

        return NutrientsData::from([
            "headings" => [
                "",
                $this->ProductDeclarations[0]["NutritionInformation"][
                    "Voedingswaarden"
                ]["Algemeen"]["Standaardeenheid"],
            ],
            "rows" => Arr::flatten(
                depth: 1,
                array: array_map(
                    fn($row) => [
                        $rowFn($row["MainValue"]),
                        ...array_map($rowFn, $row["SubValues"]),
                    ],
                    $this->Nutrition
                )
            ),
        ]);
    }

    public function ingredients(): string|null
    {
        if (empty($this->ProductDeclarations)) {
            return null;
        }

        return $this->ProductDeclarations[0]["ProductIngredients"][0]["Text"];
    }

    public function allergens(): AllergensData|null
    {
        if (empty($this->ProductDeclarations)) {
            return null;
        }

        return new AllergensData(
            array_map(
                fn($allergen) => implode(" ", [
                    $allergen["AllergenText"],
                    $allergen["AllergenDescription"],
                ]),
                $this->ProductDeclarations[0]["ProductAllergens"]
            )
        );
    }

    public function discount(): DiscountData|null
    {
        if (empty($this->ProductOffers)) {
            return null;
        }

        return new DiscountData(
            start: Carbon::parse($this->ProductOffers[0]["Offer"]["startDate"]),
            end: Carbon::parse($this->ProductOffers[0]["Offer"]["endDate"]),
            tiers: $this->approximateTiers()
        );
    }

    private function approximateTiers(): array
    {
        $original = $this->ProductOffers[0]["RegularPrice"];
        $offer = $this->ProductOffers[0]["OfferPrice"];

        return [
            new DiscountTierData(
                description: "van " .
                    Number::currency($original, "EUR") .
                    " voor " .
                    Number::currency($offer, "EUR"),
                amount: $offer,
                unit: DiscountUnit::Money,
                size: 1
            ),
        ];
    }
}
