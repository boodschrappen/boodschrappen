<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\DiscountsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\StoresRelationManager;
use App\Models\Product;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'product';

    protected static ?string $pluralModelLabel = 'producten';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TagsInput::make('gtins')
                    ->nestedRecursiveRules(['numeric']),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('summary')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\ImageColumn::make('image')
                        ->hidden(),

                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('name')
                            ->weight(FontWeight::Bold)
                            ->size(TextColumnSize::Large)
                            ->searchable(),
                        Tables\Columns\TextColumn::make('productStores.original_price')
                            ->money('EUR'),
                        Tables\Columns\TextColumn::make('stores.name')
                            ->badge()
                            ->wrap()
                            ->searchable(),
                    ]),
                ])
                    ->from('md'),
            ])
            ->contentGrid([
                'md' => 2,
                'lg' => 3,
            ])
            ->filters([
                SelectFilter::make('stores')
                    ->multiple()
                    ->options(Store::pluck('name', 'id'))
                    ->relationship('stores', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            StoresRelationManager::class,
            DiscountsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
