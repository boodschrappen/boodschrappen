<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\ShoppingListResource\Pages\ListShoppingLists;
use App\Filament\Resources\ShoppingListResource\Pages;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\ShoppingListItem;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class ShoppingListResource extends Resource
{
    protected static ?string $model = ShoppingListItem::class;

    protected static string | \BackedEnum | null $navigationIcon = "heroicon-o-shopping-cart";

    protected static ?string $navigationLabel = "Lijstje";

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = "item";

    protected static ?string $pluralModelLabel = "lijstje";

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            TextInput::make("amount")
                ->label("Aantal")
                ->integer()
                ->default(1)
                ->minValue(1)
                ->autofocus(),
            Select::make("product_store_id")
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
                Group::make("storeProduct.store.name")->label(
                    "Winkel"
                )
            )
            ->recordClasses(
                fn(ShoppingListItem $record) => $record->checked
                    ? "opacity-60"
                    : ""
            )
            ->columns([
                Split::make([
                    CheckboxColumn::make("checked")
                        ->label("")
                        ->grow(false),
                    ImageColumn::make(
                        "storeProduct.product.image"
                    )
                        ->grow(false)
                        ->width(64)
                        ->extraImgAttributes(["class" => "object-contain!"]),
                    Stack::make([
                        TextColumn::make(
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
                        TextColumn::make(
                            "amount"
                        )->formatStateUsing(
                            fn($state) => $state .
                                ($state === 1 ? " stuk" : " stuks")
                        ),
                    ]),
                ]),
            ])
            ->recordAction(null)
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::Small)
                    ->hiddenLabel()
                    ->extraAttributes(["class" => "ml-auto"]),
                DeleteAction::make()
                    ->modal(false)
                    ->hiddenLabel(),
            ])
            ->paginated(false);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            "index" => ListShoppingLists::route("/"),
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
