<?php

namespace App\Containers\AppSection\Resume\Data\Repositories;

use App\Containers\AppSection\Resume\Models\ResumeEducation;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of ResumeEducation
 *
 * @extends ParentRepository<TModel>
 */
final class ResumeEducationRepository extends ParentRepository
{
    /**
     * @return class-string<ResumeEducation>
     */
    public function model(): string
    {
        return ResumeEducation::class;
    }
}
