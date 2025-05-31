<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShoppingListResource\Pages;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\ShoppingListItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class ShoppingListResource extends Resource
{
    protected static ?string $model = ShoppingListItem::class;

    protected static ?string $navigationIcon = "heroicon-o-shopping-bag";

    protected static ?string $navigationLabel = "Lijstje";

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = "item";

    protected static ?string $pluralModelLabel = "lijstje";

    public static function form(Form $form): Form
    {
        return $form->columns(1)->schema([
            Forms\Components\TextInput::make("amount")
                ->label("Aantal")
                ->numeric()
                ->default(1)
                ->autofocus(),
            Forms\Components\Select::make("product_store_id")
                ->label("Product")
                ->allowHtml()
                ->required()
                ->searchable()
                ->autofocus(fn(string $operation) => $operation === "create")
                ->getSearchResultsUsing(
                    fn(string $search): array => Product::search($search)
                        ->take(10)
                        ->get()
                        ->filter(fn($product) => $product->stores?->count() > 0)
                        ->mapWithKeys(
                            fn($product) => [
                                $product->stores->first()->pivot
                                    ->id => self::getCleanOptionString(
                                    $product
                                ),
                            ]
                        )
                        ->toArray()
                )
                ->getOptionLabelUsing(function ($value): string {
                    $product = ProductStore::find($value)->product;

                    return self::getCleanOptionString($product);
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->orderBy("checked", "asc"))
            ->defaultGroup(
                Tables\Grouping\Group::make("storeProduct.store.name")->label(
                    "Winkel"
                )
            )
            ->recordClasses(
                fn(ShoppingListItem $record) => $record->checked
                    ? "opacity-60"
                    : ""
            )
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\CheckboxColumn::make("checked")
                        ->label("")
                        ->grow(false),
                    Tables\Columns\ImageColumn::make(
                        "storeProduct.product.image"
                    )
                        ->grow(false)
                        ->width(64)
                        ->extraImgAttributes(["class" => "!object-contain"]),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make(
                            "storeProduct.product.name"
                        )
                            ->default(fn($record) => $record->description)
                            ->weight("bold")
                            ->url(
                                fn($record) => ProductResource::getUrl("view", [
                                    "record" =>
                                        $record->storeProduct->product_id,
                                ])
                            ),
                        Tables\Columns\TextColumn::make(
                            "amount"
                        )->formatStateUsing(
                            fn($state) => $state .
                                ($state === 1 ? " stuk" : " stuks")
                        ),
                    ]),
                ]),
            ])
            ->recordAction(null)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalWidth(MaxWidth::Small)
                    ->hiddenLabel()
                    ->extraAttributes(["class" => "ml-auto"]),
                Tables\Actions\DeleteAction::make()
                    ->modal(false)
                    ->hiddenLabel(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListShoppingLists::route("/"),
        ];
    }

    public static function getCleanOptionString($product): string
    {
        return new HtmlString(
            Blade::render(
                <<<'blade'
<div class="flex rounded-md relative">
    <div class="flex">
        <div class="px-2 py-3">
            <div class="h-10 w-10">
                <img src="{{ $product->image }}" alt="{{ $product->name }}" role="img" class="h-full w-full object-cover" />
            </div>
        </div>

        <div class="flex flex-col justify-center pl-3 py-2">
            <p class="text-sm font-bold pb-1">{{ $product->name }}</p>
            <div class="flex flex-col items-start">
                <p class="text-xs leading-5">{{ $product->stores?->pluck("name")->join(", ") }}</p>
            </div>
        </div>
    </div>
</div>
blade
                ,
                ["product" => $product]
            )
        );
    }
}
