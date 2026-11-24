<?php

namespace App\Containers\AppSection\Resume\Data\Repositories;

use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of ResumeExperience
 *
 * @extends ParentRepository<TModel>
 */
final class ResumeExperienceRepository extends ParentRepository
{
    /**
     * @return class-string<ResumeExperience>
     */
    public function model(): string
    {
        return ResumeExperience::class;
    }
}
