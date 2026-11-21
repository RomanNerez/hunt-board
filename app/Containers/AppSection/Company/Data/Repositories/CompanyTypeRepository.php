<?php

namespace App\Containers\AppSection\Company\Data\Repositories;

use App\Containers\AppSection\Company\Models\CompanyType;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of CompanyType
 *
 * @extends ParentRepository<TModel>
 */
final class CompanyTypeRepository extends ParentRepository
{
    /**
     * @return class-string<CompanyType>
     */
    public function model(): string
    {
        return CompanyType::class;
    }
}
