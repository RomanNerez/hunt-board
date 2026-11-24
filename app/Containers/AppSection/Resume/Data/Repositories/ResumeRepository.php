<?php

namespace App\Containers\AppSection\Resume\Data\Repositories;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Resume
 *
 * @extends ParentRepository<TModel>
 */
final class ResumeRepository extends ParentRepository
{
    /**
     * @return class-string<Resume>
     */
    public function model(): string
    {
        return Resume::class;
    }
}
