<?php

namespace App\Containers\AppSection\Profile\Providers;

use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Ship\Parents\Providers\ServiceProvider as ParentServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\Translatable\Facades\Translatable;

final class ProfileServiceProvider extends ParentServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        Translatable::fallback(fallbackLocale: ProfileLocale::primary()->value);

        Relation::morphMap([
            'profile_skill_group' => ProfileSkillGroup::class,
            'profile_project' => ProfileProject::class,
        ]);
    }
}
