<?php

namespace App\Data;

use App\Contracts\ProductData;
use App\Data\Nutrients\AllergensData;
use App\Data\Nutrients\NutrientsData;
use App\Data\Promotions\PromotionData;
use App\Models\Product;
use App\Models\ProductStore;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class VomarProductData extends Data implements ProductData
{
    public function __construct(
        public int $articleNumber,
        public float $price,
        public string $description = "",
        public Optional|null|string $detailedDescription = "",
        public Optional|null|int $departmentNumberWebsite = null,
        public Optional|null|int $mainGroupNumberWebsite = null,
        public Optional|null|int $subGroupNumberWebsite = null,
        public Optional|null|string $eanPrimary = null,
        public Optional|null|array $eanSynonyms = null,
        public Optional|null|string $priceDefaultAmount = null,
        public Optional|null|array $images = null,
        public Optional|null|string $brand = null,
        public Optional|null|int $relevancy = null,
        public Optional|null|bool $discountDeal = null,

        // Only available on details page.
        public Optional|null|array $nutritions = null,
        public Optional|null|array $ingredients = null,
        public Optional|null|string $allergiesWarning = null
    ) {}

    public static function fromModel(ProductStore $storeProduct): self
    {
        return self::factory()
            ->withoutOptionalValues()
            ->from([
                "articleNumber" => $storeProduct->raw_identifier,
                "description" => $storeProduct->product->name,
                "detailedDescription" => $storeProduct->product->description,
                "price" => $storeProduct->original_price,
            ]);
    }

    public function toProduct(): Product
    {
        return new Product([
            "gtins" => array_merge(
                [$this->eanPrimary],
                Arr::wrap($this->eanSynonyms)
            ),
            "name" => $this->detailedDescription,
            "summary" => "",
            "description" => $this->description,
            "image" => $this->images
                ? "https://d3vricquk1sjgf.cloudfront.net/articles/" .
                    $this->images[0]["imageUrl"]
                : null,
            "ingredients" => $this->ingredients(),
            "nutrients" => $this->nutrients(),
            "allergens" => $this->allergens()?->allergens,
        ]);
    }

    public function toStoreProduct(): ProductStore
    {
        return new ProductStore([
            "raw_identifier" => $this->articleNumber,
            "reduced_price" => null,
            "original_price" => $this->price,
            "raw" => $this->toArray(),
        ]);
    }

    public function nutrients(): NutrientsData|null
    {
        return NutrientsData::from([
            "headings" => array_merge(
                [""],
                $this->nutritions["headings"] ?? []
            ),
            "rows" => array_map(
                fn($row) => [$row["name"] ?? "", ...$row["values"]],
                $this->nutritions["rows"] ?? []
            ),
        ]);
    }

    public function ingredients(): string|null
    {
        return $this->ingredients ? implode(", ", $this->ingredients) : null;
    }

    public function allergens(): AllergensData|null
    {
        return $this->allergiesWarning
            ? new AllergensData([$this->allergiesWarning])
            : null;
    }

    public function promotion(): PromotionData|null
    {
        // Vomar has no data apart from the key `discountDeal` which is also not up-to-date.
        return null;
    }
}
