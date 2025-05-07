<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StoresRelationManager extends RelationManager
{
    protected static string $relationship = 'stores';
    
    protected static bool $isLazy = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->heading('Winkels')
            ->defaultSort('original_price')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->label('Naam'),
                Tables\Columns\TextColumn::make('original_price')
                    ->sortable()
                    ->label('Originele prijs')
                    ->money('EUR'),
                Tables\Columns\TextColumn::make('reduced_price')
                    ->sortable()
                    ->label('Gereduceerde prijs')
                    ->money('EUR'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
