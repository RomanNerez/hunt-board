<?php

namespace App\Containers\AppSection\Company\Data\Repositories;

use App\Containers\AppSection\Company\Models\Domain;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Domain
 *
 * @extends ParentRepository<TModel>
 */
final class DomainRepository extends ParentRepository
{
    /**
     * @return class-string<Domain>
     */
    public function model(): string
    {
        return Domain::class;
    }
}
