<?php

namespace App\Containers\AppSection\Stack\Data\Repositories;

use App\Containers\AppSection\Stack\Models\Stack;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Stack
 *
 * @extends ParentRepository<TModel>
 */
final class StackRepository extends ParentRepository
{
    /**
     * @return class-string<Stack>
     */
    public function model(): string
    {
        return Stack::class;
    }
}
