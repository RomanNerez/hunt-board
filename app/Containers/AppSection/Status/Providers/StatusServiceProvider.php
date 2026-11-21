<?php

namespace App\Containers\AppSection\Status\Providers;

use App\Ship\Parents\Providers\ServiceProvider as ParentServiceProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;

final class StatusServiceProvider extends ParentServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        FilamentAsset::register([
            Css::make('status-badge', __DIR__ . '/../UI/WEB/Assets/css/status-badge.css'),
        ]);
    }
}
