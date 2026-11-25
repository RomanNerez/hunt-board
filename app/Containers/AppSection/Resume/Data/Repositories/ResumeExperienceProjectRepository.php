<?php

namespace App\Containers\AppSection\Resume\Data\Repositories;

use App\Containers\AppSection\Resume\Models\ResumeExperienceProject;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of ResumeExperienceProject
 *
 * @extends ParentRepository<TModel>
 */
final class ResumeExperienceProjectRepository extends ParentRepository
{
    /**
     * @return class-string<ResumeExperienceProject>
     */
    public function model(): string
    {
        return ResumeExperienceProject::class;
    }
}
