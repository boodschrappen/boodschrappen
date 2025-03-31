<?php

namespace App\Data;

use App\Contracts\ProductData;
use App\Models\Product;
use App\Models\ProductStore;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class DekamarktProductData extends Data implements ProductData
{
    public function __construct(
        public int                  $ProductID,
        public string               $MainDescription,
        public array                $ProductPrices,
        public Optional|null|string $ProductNumber,
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
        public Optional|null|array  $ProductPictures = null,
        public Optional|null|array  $Logos = null,
        public Optional|null|array  $WebSubGroups = null,
        public Optional|null|array  $ProductOffers = null,
        public Optional|null|array  $StoreAssortments = null,
        public Optional|null|bool   $IsSingleUsePlastic = null,

        // Only available on details page.
        public Optional|null|array  $Nutrition = null,
        public Optional|null|array  $ProductDeclarations = null,
        public Optional|null|array  $ProductBarcodes = null,
    ) {}

    public function toProduct(): Product
    {
        return new Product([
            'gtins'       => $this->ProductBarcodes ? [$this->ProductBarcodes[0]['Barcode']] : [],
            'name'        => $this->Brand . ' ' . $this->MainDescription,
            'summary'     => $this->SubDescription ?? '',
            'description' => '',
            'image'       => $this->ProductPictures ? $this->ProductPictures[0]['Url'] : $this->ProductPicture['Url'],
        ]);
    }

    public function toStoreProduct(): ProductStore
    {
        return new ProductStore([
            'raw_identifier' => $this->ProductID,
            'reduced_price' => $this->ProductPrices[0]['Price'],
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
