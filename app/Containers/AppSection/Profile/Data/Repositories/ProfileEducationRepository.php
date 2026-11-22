<?php

namespace App\Containers\AppSection\Profile\Data\Repositories;

use App\Containers\AppSection\Profile\Models\ProfileEducation;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of ProfileEducation
 *
 * @extends ParentRepository<TModel>
 */
final class ProfileEducationRepository extends ParentRepository
{
    /**
     * @return class-string<ProfileEducation>
     */
    public function model(): string
    {
        return ProfileEducation::class;
    }
}
