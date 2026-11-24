<?php

namespace App\Containers\AppSection\Vacancy\Providers;

use App\Ship\Parents\Providers\ServiceProvider as ParentServiceProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;

final class VacancyServiceProvider extends ParentServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        FilamentAsset::register([
            Css::make('vacancy-notes', __DIR__ . '/../UI/WEB/Assets/css/vacancy-notes.css'),
        ]);
    }
}
