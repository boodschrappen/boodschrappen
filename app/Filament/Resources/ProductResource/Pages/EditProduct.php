<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\ProductResource;
use App\Jobs\FetchProduct;
use App\Models\Product;
use App\Models\ProductStore;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Throwable;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('fetch')
                ->label('Product heropvragen')
                ->action(function (Product $record) {
                    try {
                        $record->productStores->each(fn(ProductStore $productStore) => FetchProduct::dispatch($productStore));

                        Notification::make()
                            ->title('Product wordt opgehaald')
                            ->success()
                            ->send();

                        redirect(request()->header('Referer'));
                    } catch (Throwable $e) {
                        Notification::make()
                            ->title('Er is iets misgegaan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make(),
        ];
    }
}
