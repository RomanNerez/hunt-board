<?php

namespace App\Containers\AppSection\Profile\Providers;

use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Ship\Parents\Providers\ServiceProvider as ParentServiceProvider;
use Spatie\Translatable\Facades\Translatable;

final class ProfileServiceProvider extends ParentServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        Translatable::fallback(fallbackLocale: ProfileLocale::primary()->value);
    }
}
