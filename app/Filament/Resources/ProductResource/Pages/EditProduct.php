<?php

namespace App\Filament\Resources\ProductResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\ProductResource;
use App\Jobs\FetchProduct;
use App\Models\Product;
use App\Models\ProductStore;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
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
                        $record->productStores->each(function (ProductStore $productStore) {
                            $crawler = Str::of($productStore->store->slug)
                                ->title()
                                ->prepend('App\Services\Crawlers\\')
                                ->append('Crawler')
                                ->toString();
                            Context::add('crawler', $crawler);
                            FetchProduct::dispatch($productStore);
                        });

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
