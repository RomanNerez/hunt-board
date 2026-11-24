<?php

namespace App\Containers\AppSection\Resume\Data\Repositories;

use App\Containers\AppSection\Resume\Models\ResumeLink;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of ResumeLink
 *
 * @extends ParentRepository<TModel>
 */
final class ResumeLinkRepository extends ParentRepository
{
    /**
     * @return class-string<ResumeLink>
     */
    public function model(): string
    {
        return ResumeLink::class;
    }
}
