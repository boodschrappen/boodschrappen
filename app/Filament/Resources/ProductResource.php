<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\DiscountsRelationManager;
use App\Filament\Resources\ProductResource\RelationManagers\StoresRelationManager;
use App\Models\Product;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'product';

    protected static ?string $pluralModelLabel = 'producten';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'store_names' => 'Winkels',
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\ImageEntry::make('image')
                    ->hiddenLabel()
                    ->height('auto')
                    ->width('100%')
                    ->alignCenter()
                    ->extraImgAttributes(['class' => 'shadow rounded-xl overflow-hidden p-3 bg-white max-w-sm'])
                    ->defaultImageUrl('https://static.ah.nl/dam/product/AHI_4354523130303539323232?revLabel=1&rendition=800x800_JPG_Q90&fileType=binary')
                    ->columnSpan([
                        'md' => 1
                    ]),
                InfoLists\Components\Group::make([
                    Infolists\Components\TextEntry::make('name')
                        ->hiddenLabel()
                        ->weight(FontWeight::Bold)
                        ->size('text-2xl'),
                    Infolists\Components\TextEntry::make('summary')
                        ->hiddenLabel()
                        ->html(),
                ])->columnSpan([
                    'md' => 2
                ])
            ])
            ->columns(['md' => 3, 'lg' => 3]);
    }

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
            ->searchable()
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\ImageColumn::make('image')
                        ->grow(false)
                        ->size(60)
                        ->extraImgAttributes(['loading' => 'lazy']),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('name')
                            ->weight(FontWeight::Bold)
                            ->size(TextColumnSize::Large),
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
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
