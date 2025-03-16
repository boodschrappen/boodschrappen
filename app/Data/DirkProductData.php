<?php

namespace App\Data;

use App\Contracts\ProductData;
use App\Models\Product;
use App\Models\ProductStore;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class DirkProductData extends Data implements ProductData
{
    public function __construct(
        public int                  $ProductID,
        public string               $ProductNumber,
        public string               $MainDescription,
        public array                $ProductPrices,
        public Optional|null|string $SubDescription = null,
        public Optional|null|string $CommercialContent = null,
        public Optional|null|string $MaxPerCustomer = null,
        public Optional|null|string $DepositMoney = null,
        public Optional|null|string $Brand = null,
        public Optional|null|bool   $ProductOnline = null,
        public Optional|null|bool   $ProductInStock = null,
        public Optional|null|bool   $WeightArticle = null,
        public Optional|null|bool   $ScaleIndicator = null,
        public Optional|null|string $PiecesInWeight = null,
        public Optional|null|string $AlcoholPercentage = null,
        public Optional|null|bool   $TemporaryNotAvailable = null,
        public Optional|null|string $PublicationAfter = null,
        public Optional|null|string $WeightOfPeicesInWeight = null,
        public Optional|null|array  $ProductPicture = null,
        public Optional|null|array  $Logos = null,
        public Optional|null|array  $WebSubGroups = null,
        public Optional|null|array  $ProductOffers = null,
        public Optional|null|array  $StoreAssortments = null,
        public Optional|null|bool   $IsSingleUsePlastic = null,
    ) {}

    public function toProduct(): Product
    {
        return new Product([
            'gtins'       => "[]",
            'name'        => $this->MainDescription,
            'summary'     => $this->SubDescription ?? $this->Brand ?? '',
            'description' => '',
        ]);
    }

    public function toStoreProduct(): ProductStore
    {
        return new ProductStore([
            'raw_identifier' => $this->ProductID,
            'reduced_price' => null,
            'original_price' => $this->ProductPrices[0]['RegularPrice'],
            'raw' => json_encode($this->toArray()),
        ]);
    }

    public static function fromModel(ProductStore $storeProduct): self
    {
        return self::from([
            'id' => $storeProduct->raw_identifier,
            'MainDescription' => $storeProduct->product->name,
            'ProductPrices' => [
                'RegularPrice' => $storeProduct->original_price,
            ],
        ]);
    }
}
