<?php

namespace App\Filament\Pages;

use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class FrontPage extends Page
{
    protected string $view = 'filament.pages.front-page';

    protected static string $routePath = '/';

    protected static bool $shouldRegisterNavigation = false;

    public string $query = '';

    public static function getRoutePath(Panel $panel): string
    {
        return static::$routePath;
    }

    public function getTitle(): string
    {
        return '';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('query')
                    ->hiddenLabel()
                    ->extraAlpineAttributes(['wire:keydown.enter' => 'search', 'class' => 'w-96! py-3'])
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
