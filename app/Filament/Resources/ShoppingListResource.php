<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShoppingListResource\Pages;
use App\Filament\Resources\ShoppingListResource\RelationManagers;
use App\Models\ProductStore;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make("amount")->numeric(),
                Forms\Components\TextInput::make("description")->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->orderBy("checked", "asc"))
            ->defaultGroup("storeProduct.store.name")
            ->recordClasses(
                fn(ShoppingListItem $record) => $record->checked
                    ? "opacity-60"
                    : ""
            )
            ->columns([
                Tables\Columns\CheckboxColumn::make("checked")->label(""),
                Tables\Columns\TextInputColumn::make("amount")->inputMode(
                    "number"
                ),
                Tables\Columns\TextColumn::make("storeProduct.product.name")
                    ->default(fn($record) => $record->description)
                    ->grow(),
            ])
            ->actions([Tables\Actions\DeleteAction::make()->hiddenLabel()]);
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
}
