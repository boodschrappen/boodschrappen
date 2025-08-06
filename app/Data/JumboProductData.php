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
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class JumboProductData extends Data implements ProductData
{
    public function __construct(
        public string $id,
        public string $title,
        public Optional|null|array $price = null,
        public Optional|null|array $quantityOptions = null,
        public Optional|null|bool $available = null,
        public Optional|null|string $productType = null,
        public Optional|null|array $crossSellSKUList = null,
        public Optional|null|bool $nixProduct = null,
        public Optional|null|array $imageInfo = null,
        public Optional|null|string $unavailabilityReason = null,
        public Optional|null|array $badgesToDisplay = null,
        public Optional|null|bool $sample = null,
        public Optional|null|array $availability = null,
        public Optional|null|array $allergens = null,
        public Optional|null|array $surcharges = null,

        // Only available on details page.
        public Optional|null|array $prices = null,
        public Optional|null|array $promotion = null,
        public Optional|null|array $ingredientInfo = null,
        public Optional|null|array $nutritionalInformation = null,
        public Optional|null|string $allergyText = null,
    ) {}

    public static function fromModel(ProductStore $storeProduct): self
    {
        return self::from([
            "id" => $storeProduct->raw_identifier,
            "title" => $storeProduct->product->name,
            "price" => $storeProduct->original_price,
        ]);
    }

    public function toProduct(): Product
    {
        $image = $this->imageInfo
            ? $this->imageInfo["primaryView"][0]["url"]
            : null;
        $result = preg_match("/(\d+)_[^_]+\.png/i", $image, $gtins);

        return new Product([
            "gtins" => $result > 0 ? [$gtins[1]] : [],
            "name" => $this->title,
            "summary" => "",
            "description" => "",
            "image" => $image,
            "ingredients" => $this->ingredients(),
            "nutrients" => $this->nutrients(),
            "allergens" => $this->allergens()?->allergens,
        ]);
    }

    public function toStoreProduct(): ProductStore
    {
        $price = null;
        if ($this->price || $this->prices) {
            $price = $this->price ?: $this->prices["price"]["amount"] / 100;
        }

        return new ProductStore([
            "raw_identifier" => $this->id,
            "reduced_price" => null,
            "original_price" => $price,
            "raw" => $this->toArray(),
        ]);
    }

    public function nutrients(): NutrientsData|null
    {
        if (empty($this->nutritionalInformation)) {
            return null;
        }

        $entries =
            $this->nutritionalInformation[0]["nutritionalData"]["entries"];

        return NutrientsData::from([
            "headings" => ["", "Per 100 gram", "Referentie-inname"],
            "rows" => array_map(
                fn($row) => [
                    $row["name"],
                    $row["valuePer100g"],
                    $row["valuePerPortion"],
                ],
                $entries ?? [],
            ),
        ]);
    }

    public function ingredients(): string|null
    {
        if (empty($this->ingredientInfo)) {
            return null;
        }

        return implode(
            ", ",
            array_map(
                fn($ingredient) => $ingredient["name"],
                $this->ingredientInfo[0]["ingredients"],
            ),
        );
    }

    public function allergens(): AllergensData|null
    {
        if (empty($this->allergyText)) {
            return null;
        }

        return new AllergensData([$this->allergyText]);
    }

    public function discount(): DiscountData|null
    {
        if (!$this->promotion) {
            return null;
        }

        return new DiscountData(
            start: Carbon::parse($this->promotion["fromDate"] / 1000),
            end: Carbon::parse($this->promotion["toDate"] / 1000),
            tiers: $this->approximateTiers(),
        );
    }

    private function approximateTiers(): Collection
    {
        $promotionTypes = collect([
            "2e halve prijs" => fn() => [
                "amount" => 25,
                "size" => 2,
                "unit" => DiscountUnit::Percentage,
            ],
            "(\\d+)\\svoor\\s€\\s?([\\d.]+)" => function (array $match) {
                if (!empty($match) && ($size = $match[1])) {
                    return [
                        "amount" => $match[2],
                        "size" => $size,
                        "unit" => "money",
                    ];
                }

                return null;
            },
            "(\\d+)%" => fn(array $match) => [
                "amount" => $match[1],
                "size" => 1,
                "unit" => DiscountUnit::Percentage,
            ],
            "€\\s?(\\d+)" => fn(array $match) => [
                "amount" => $match[1],
                "size" => 1,
                "unit" => DiscountUnit::Money,
            ],
            "(\\d+)\\s?\\+\\s?(\\d+)\\sgratis" => function (array $match) {
                $minimum = $match[1];
                $additional = $match[2];
                if (!$minimum || !$additional) {
                    return null;
                }

                $size = $minimum + $additional;
                return [
                    "amount" => ($additional / $size) * 100,
                    "size" => $size,
                    "unit" => DiscountUnit::Percentage,
                ];
            },
        ]);

        return collect($this->promotion["tags"])
            ->map(function ($tag) use ($promotionTypes) {
                foreach ($promotionTypes as $regex => $fn) {
                    $match = preg_match($regex, $tag, PREG_UNMATCHED_AS_NULL);
                    if (!$match) {
                        continue;
                    }
                    return $fn($match);
                }
            })
            ->filter(fn($t) => $t !== null)
            ->map(fn($t) => DiscountTierData::from($t));
    }
}
