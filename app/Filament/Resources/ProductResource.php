<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Infolists\Components\ImageEntry;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Support\Enums\TextSize;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\ViewProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\DiscountsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\StoresRelationManager;
use App\Filament\Tables\Actions\AddToListAction;
use App\Infolists\Components\TableEntry;
use App\Models\Product;
use App\Tables\Columns\DiscountsColumn;
use App\Tables\Columns\WhereToBuyColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | \BackedEnum | null $navigationIcon = "heroicon-o-rectangle-stack";

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = "product";

    protected static ?string $pluralModelLabel = "producten";

    protected static ?string $recordTitleAttribute = "name";

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            "store_names" => "Winkels",
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                ImageEntry::make("image")
                    ->hiddenLabel()
                    ->hidden(fn($state) => empty($state))
                    ->imageHeight("auto")
                    ->imageWidth("100%")
                    ->alignCenter()
                    ->extraImgAttributes([
                        "class" =>
                            "shadow-sm rounded-xl overflow-hidden p-3 bg-white max-w-sm",
                    ])
                    ->columnSpan([
                        "md" => 1,
                    ]),
                Group::make([
                    TextEntry::make("name")
                        ->hiddenLabel()
                        ->weight(FontWeight::Bold)
                        ->size("text-2xl"),
                    TextEntry::make("summary")
                        ->hiddenLabel()
                        ->hidden(fn($state) => empty($state))
                        ->html(),
                    TextEntry::make("ingredients")
                        ->label("Ingrediënten")
                        ->hidden(fn($state) => empty($state)),
                    TextEntry::make("allergens")
                        ->label("Allergenen")
                        ->hidden(fn($state) => empty($state)),
                    TableEntry::make("nutrients")
                        ->label("Voedingswaarden")
                        ->hidden(fn($state) => empty($state)),
                ])->columnSpan([
                    "md" => 2,
                ]),
            ])
            ->columns(["md" => 3, "lg" => 3]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TagsInput::make("gtins")->nestedRecursiveRules([
                "numeric",
            ]),
            TextInput::make("name")
                ->required()
                ->maxLength(255),
            TextInput::make("summary")->required(),
            TextInput::make("description")->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchable()
            ->recordActionsAlignment("center")
            ->modifyQueryUsing(fn(Builder $query) => $query->with('discounts.tiers', 'productStores.store'))
            ->columns([
                DiscountsColumn::make("discounts.*.tiers")->extraCellAttributes(
                    ["class" => "absolute"]
                ),
                Stack::make([
                    ImageColumn::make("image")
                        ->imageSize(128)
                        ->grow(false)
                        ->extraImgAttributes([
                            "loading" => "lazy",
                            "class" => "m-2",
                        ]),
                    Stack::make([
                        TextColumn::make("name")
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large),
                        WhereToBuyColumn::make(
                            "productStores"
                        )->extraAttributes(["class" => "mt-2"]),
                    ])->alignBetween(),
                ])
                    ->space(2)
                    ->extraAttributes(["class" => "flex-row! sm:flex-col!"])
                    ->alignCenter(),
            ])
            ->contentGrid([
                "sm" => 2,
                "md" => 3,
                "lg" => 4,
            ])
            ->filters([
                SelectFilter::make("store_id")
                    ->label("Store")
                    ->multiple()
                    ->preload()
                    ->relationship("stores", "name"),
                Filter::make("discounts")
                    ->label("Alleen aanbiedingen")
                    ->query(
                        fn($query, $data) => $query->when(
                            $data["isActive"] === true,
                            fn($query) => $query->has("discounts")
                        )
                    ),
            ])
            ->recordActions([
                ViewAction::make()->hiddenLabel()->icon(null),
                EditAction::make()->hiddenLabel(),
                AddToListAction::make()
                    ->hiddenLabel()
                    ->tooltip("Voeg toe aan je lijstje")
                    ->extraAttributes(["class" => "mt-2 ml-auto"])
                    ->button(),
            ])
            ->paginationPageOptions([5, 10, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [StoresRelationManager::class, DiscountsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            "index" => ListProducts::route("/"),
            "create" => CreateProduct::route("/create"),
            "view" => ViewProduct::route("/{record}"),
            "edit" => EditProduct::route("/{record}/edit"),
        ];
    }
}
