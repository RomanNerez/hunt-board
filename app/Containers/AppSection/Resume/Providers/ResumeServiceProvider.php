<?php

namespace App\Containers\AppSection\Resume\Providers;

use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Ship\Parents\Providers\ServiceProvider as ParentServiceProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Relations\Relation;

final class ResumeServiceProvider extends ParentServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        FilamentAsset::register([
            Css::make('resume-cards', __DIR__ . '/../UI/WEB/Assets/css/resume-cards.css'),
        ]);

        Relation::morphMap([
            'resume_experience' => ResumeExperience::class,
        ]);
    }
}
