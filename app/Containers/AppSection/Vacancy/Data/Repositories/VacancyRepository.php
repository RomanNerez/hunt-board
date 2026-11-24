<?php

namespace App\Containers\AppSection\Vacancy\Data\Repositories;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of Vacancy
 *
 * @extends ParentRepository<TModel>
 */
final class VacancyRepository extends ParentRepository
{
    /**
     * @return class-string<Vacancy>
     */
    public function model(): string
    {
        return Vacancy::class;
    }
}
