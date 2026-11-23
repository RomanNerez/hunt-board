<?php

namespace App\Containers\AppSection\Profile\Data\Repositories;

use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of ProfileWorkExperience
 *
 * @extends ParentRepository<TModel>
 */
final class ProfileWorkExperienceRepository extends ParentRepository
{
    /**
     * @return class-string<ProfileWorkExperience>
     */
    public function model(): string
    {
        return ProfileWorkExperience::class;
    }
}
