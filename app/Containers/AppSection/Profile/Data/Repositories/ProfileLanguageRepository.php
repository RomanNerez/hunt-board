<?php

namespace App\Containers\AppSection\Profile\Data\Repositories;

use App\Containers\AppSection\Profile\Models\ProfileLanguage;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of ProfileLanguage
 *
 * @extends ParentRepository<TModel>
 */
final class ProfileLanguageRepository extends ParentRepository
{
    /**
     * @return class-string<ProfileLanguage>
     */
    public function model(): string
    {
        return ProfileLanguage::class;
    }
}
