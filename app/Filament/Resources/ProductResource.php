<?php

namespace App\Filament\Resources;

use App\Data\AhProductData;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\DiscountsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\StoresRelationManager;
use App\Infolists\Components\TableEntry;
use App\Models\Product;
use App\Models\ShoppingListItem;
use Fauzie811\FilamentListEntry\Infolists\Components\ListEntry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = "heroicon-o-rectangle-stack";

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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\ImageEntry::make("image")
                    ->hiddenLabel()
                    ->hidden(fn($state) => empty($state))
                    ->height("auto")
                    ->width("100%")
                    ->alignCenter()
                    ->extraImgAttributes([
                        "class" =>
                            "shadow rounded-xl overflow-hidden p-3 bg-white max-w-sm",
                    ])
                    ->columnSpan([
                        "md" => 1,
                    ]),
                InfoLists\Components\Group::make([
                    Infolists\Components\TextEntry::make("name")
                        ->hiddenLabel()
                        ->weight(FontWeight::Bold)
                        ->size("text-2xl"),
                    Infolists\Components\TextEntry::make("summary")
                        ->hiddenLabel()
                        ->hidden(fn($state) => empty($state))
                        ->html(),
                    TextEntry::make("ingredients")
                        ->label("Ingrediënten")
                        // ->inlineLabel()
                        ->hidden(fn($state) => empty($state)),
                    TextEntry::make("allergens")
                        ->label("Allergenen")
                        // ->inlineLabel()
                        ->hidden(fn($state) => empty($state)),
                    // ->formatStateUsing(
                    //     fn($record) => implode(", ", $record->allergens)
                    // ),
                    TableEntry::make("nutrients")
                        ->label("Voedingswaarden")
                        // ->inlineLabel()
                        ->hidden(fn($state) => empty($state)),
                ])->columnSpan([
                    "md" => 2,
                ]),
            ])
            ->columns(["md" => 3, "lg" => 3]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TagsInput::make("gtins")->nestedRecursiveRules([
                "numeric",
            ]),
            Forms\Components\TextInput::make("name")
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make("summary")->required(),
            Forms\Components\TextInput::make("description")->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchable()
            ->actionsAlignment("center")
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ImageColumn::make("image")
                        ->size(128)
                        ->grow(false)
                        ->extraImgAttributes([
                            "loading" => "lazy",
                            "class" => "m-2",
                        ]),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make("name")
                            ->weight(FontWeight::Bold)
                            ->size(TextColumnSize::Large),
                        Tables\Columns\TextColumn::make(
                            "productStores.original_price"
                        )->money("EUR"),
                        Tables\Columns\TextColumn::make("stores.name")
                            ->badge()
                            ->wrap()
                            ->alignRight()
                            ->extraAttributes(["class" => "mt-2"]),
                    ]),
                ])
                    ->space(2)
                    ->extraAttributes(["class" => "!flex-row sm:!flex-col"])
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->hiddenLabel()->icon(null),
                Tables\Actions\EditAction::make()->hiddenLabel(),
                Tables\Actions\Action::make("add_to_list")
                    ->hiddenLabel()
                    ->tooltip("Voeg toe aan je lijstje")
                    ->badge(
                        fn($record) => ShoppingListItem::firstWhere(
                            "product_store_id",
                            $record->productStores()->first()?->id
                        )?->amount
                    )
                    ->badgeColor("info")
                    ->icon("heroicon-o-shopping-cart")
                    ->extraAttributes(["class" => "mt-2 ml-auto"])
                    ->button()
                    ->action(function (Product $record) {
                        // TODO: Put in reuable method.
                        $existingListItem = ShoppingListItem::firstWhere(
                            "product_store_id",
                            $record->productStores()->first()->id
                        );

                        if ($existingListItem) {
                            $existingListItem->amount += 1;
                            $existingListItem->save();
                        } else {
                            ShoppingListItem::create([
                                "amount" => 1,
                                // TODO: Select the cheapest option. Maybe using a scope?
                                "product_store_id" => $record
                                    ->productStores()
                                    ->first()->id,
                                "description" => $record->name,
                            ]);
                        }

                        Notification::make("list_item_added")
                            ->title(
                                "$record->name is toegevoegd aan je lijstje"
                            )
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [StoresRelationManager::class, DiscountsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            "index" => Pages\ListProducts::route("/"),
            "create" => Pages\CreateProduct::route("/create"),
            "view" => Pages\ViewProduct::route("/{record}"),
            "edit" => Pages\EditProduct::route("/{record}/edit"),
        ];
    }
}
