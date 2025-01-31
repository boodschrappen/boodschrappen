<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DiscountsRelationManager extends RelationManager
{
    protected static string $relationship = 'discounts';

    public function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\Split::make([
                    Forms\Components\DatePicker::make('start')
                        ->required(),
                    Forms\Components\DatePicker::make('end')
                        ->required(),
                    Forms\Components\Select::make('product_store_id')
                        ->label('Winkel')
                        ->options(
                            $this->ownerRecord
                                ->productStores()
                                ->with('store')
                                ->get()
                                ->pluck('store.name', 'id')
                        )
                        ->required()
                        ->columns(2)
                ]),
                Forms\Components\Repeater::make('tiers')
                    ->schema([
                        Forms\Components\TextInput::make('description')
                            ->maxLength(255),
                        Forms\Components\Split::make([
                            Forms\Components\TextInput::make('amount')
                                ->numeric(),
                            Forms\Components\Select::make('unit')
                                ->options(['money', 'percentage']),
                            Forms\Components\TextInput::make('size')
                                ->numeric(),
                        ])
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('start')
            ->columns([
                Tables\Columns\TextColumn::make('start')->date(),
                Tables\Columns\TextColumn::make('end')->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
