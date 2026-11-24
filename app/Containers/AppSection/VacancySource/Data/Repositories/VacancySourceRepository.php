<?php

namespace App\Containers\AppSection\VacancySource\Data\Repositories;

use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of VacancySource
 *
 * @extends ParentRepository<TModel>
 */
final class VacancySourceRepository extends ParentRepository
{
    /**
     * @return class-string<VacancySource>
     */
    public function model(): string
    {
        return VacancySource::class;
    }
}
