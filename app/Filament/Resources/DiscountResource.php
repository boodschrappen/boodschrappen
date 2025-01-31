<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountResource\Pages;
use App\Filament\Resources\DiscountResource\RelationManagers;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductStore;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DiscountResource extends Resource
{
    protected static ?string $model = Discount::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([
                Forms\Components\Repeater::make('tiers')
                    ->hiddenLabel()
                    ->itemLabel('Korting')
                    ->columnSpan(2)
                    ->relationship('tiers')
                    ->schema([
                        Forms\Components\TextInput::make('description')
                            ->maxLength(255),
                        Forms\Components\Split::make([
                            Forms\Components\TextInput::make('amount')
                                ->numeric(),
                            Forms\Components\Select::make('unit')
                                ->options(['money' => 'money', 'percentage' => 'percentage']),
                            Forms\Components\TextInput::make('size')
                                ->numeric(),
                        ])
                    ]),
                Forms\Components\Section::make()
                    ->columnSpan(1)
                    ->extraAttributes(['class' => 'sticky top-0'])
                    ->schema([
                        Forms\Components\DatePicker::make('start')
                            ->required(),
                        Forms\Components\DatePicker::make('end')
                            ->required(),
                        Forms\Components\Select::make('product_store_id')
                            ->label('Product in winkel')
                            ->options(
                                fn() => ProductStore::query()
                                    ->with('store', 'product')
                                    ->get()
                                    ->keyBy('id')
                                    ->map(fn($item) => "{$item->product->name} in {$item->store->name}")
                            )
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('start')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscounts::route('/'),
            'create' => Pages\CreateDiscount::route('/create'),
            'edit' => Pages\EditDiscount::route('/{record}/edit'),
        ];
    }
}
