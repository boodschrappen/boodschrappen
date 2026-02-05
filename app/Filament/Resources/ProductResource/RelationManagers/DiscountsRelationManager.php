<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Flex;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Forms\Components\Livewire;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DiscountsRelationManager extends RelationManager
{
    protected static string $relationship = 'discounts';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Flex::make([
                    DatePicker::make('start')
                        ->required(),
                    DatePicker::make('end')
                        ->required(),
                    Select::make('product_store_id')
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
                Repeater::make('tiers')
                    ->schema([
                        TextInput::make('description')
                            ->maxLength(255),
                        Flex::make([
                            TextInput::make('amount')
                                ->numeric(),
                            Select::make('unit')
                                ->options(['money', 'percentage']),
                            TextInput::make('size')
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
                TextColumn::make('start')->date(),
                TextColumn::make('end')->date(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
