<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Forms\Components\GlobalSearch;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class FrontPage extends Page
{
    protected static string $view = 'filament.pages.front-page';

    protected static string $routePath = '/';

    protected static bool $shouldRegisterNavigation = false;

    public string $query = '';

    public static function getRoutePath(): string
    {
        return static::$routePath;
    }

    public function getTitle(): string
    {
        return '';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('query')
                    ->hiddenLabel()
                    ->required()
                    ->extraAlpineAttributes(['wire:keydown.enter' => 'search', 'class' => '!w-96 py-3'])
                    ->prefixAction(
                        Action::make('search')
                            ->extraAttributes(['id' => 'search-btn'])
                            ->label('Zoek product')
                            ->icon('heroicon-m-magnifying-glass')
                            ->action('search')
                    )
                    ->suffixAction(
                        Action::make('scan')
                            ->label('Scan product')
                            ->icon('heroicon-m-qr-code')
                            ->modal()
                            ->modalWidth('md')
                            ->modalHeading('Scan een product')
                            ->modalDescription('Scan de streepjescode van een product.')
                            ->modalCancelAction(false)
                            ->modalSubmitAction(false)
                            ->modalContent(new HtmlString(
                                Blade::render(<<<'blade'
                                    <div class="overflow-hidden rounded-xl">
                                        <div class="relative flex flex-col justify-center">
                                            <span class="absolute w-full text-center">Bezig met laden...</span>
                                            <canvas
                                                id="canvas"
                                                class="z-10 w-full"
                                            ></canvas>
                                        </div>
                                    </div>

                                    @assets
                                        @vite('resources/js/scanner.js')
                                    @endassets
                                blade)
                            ))
                    )
            ]);
    }

    public function search()
    {
        $query = $this->form->getState()['query'];

        return redirect()->to(ListProducts::getUrl(['tableSearch' => $query]));
    }
}
