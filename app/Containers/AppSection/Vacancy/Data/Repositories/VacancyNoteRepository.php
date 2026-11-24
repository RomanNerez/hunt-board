<?php

namespace App\Containers\AppSection\Vacancy\Data\Repositories;

use App\Containers\AppSection\Vacancy\Models\VacancyNote;
use App\Ship\Parents\Repositories\Repository as ParentRepository;

/**
 * @template TModel of VacancyNote
 *
 * @extends ParentRepository<TModel>
 */
final class VacancyNoteRepository extends ParentRepository
{
    /**
     * @return class-string<VacancyNote>
     */
    public function model(): string
    {
        return VacancyNote::class;
    }
}
