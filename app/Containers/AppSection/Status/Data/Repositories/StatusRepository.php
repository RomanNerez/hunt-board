<?php

namespace App\Containers\AppSection\Status\Data\Repositories;

use App\Containers\AppSection\Status\Models\Status;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Status
 *
 * @extends ParentRepository<TModel>
 */
final class StatusRepository extends ParentRepository
{
    /**
     * @return class-string<Status>
     */
    public function model(): string
    {
        return Status::class;
    }
}
