<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

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

    public function search(): void
    {
        dd($this->query);
    }
}
