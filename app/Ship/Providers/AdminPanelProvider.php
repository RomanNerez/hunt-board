<?php

namespace App\Ship\Providers;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class AdminPanelProvider extends PanelProvider
{
    /**
     * @param Panel $panel
     *
     * @return Panel
     */
    public function panel(Panel $panel): Panel
    {
        $this->discoverContainerComponents($panel);

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Hunt Board')
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    /**
     * @param Panel $panel
     *
     * @return void
     */
    private function discoverContainerComponents(Panel $panel): void
    {
        $panel->discoverClusters(in: app_path('Ship/Filament/Clusters'), for: 'App\\Ship\\Filament\\Clusters');

        /** @var list<string> $directories */
        $directories = File::glob(app_path('Containers/*/*/UI/Filament'), GLOB_ONLYDIR);

        foreach ($directories as $directory) {
            $namespace = 'App\\' . str_replace('/', '\\', Str::after($directory, app_path() . '/'));

            $panel
                ->discoverClusters(in: $directory . '/Clusters', for: $namespace . '\\Clusters')
                ->discoverResources(in: $directory . '/Resources', for: $namespace . '\\Resources')
                ->discoverPages(in: $directory . '/Pages', for: $namespace . '\\Pages')
                ->discoverWidgets(in: $directory . '/Widgets', for: $namespace . '\\Widgets');
        }
    }
}
