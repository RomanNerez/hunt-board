<?php

namespace App\Containers\AppSection\Vacancy\Tasks;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class SyncVacancyContactsTask extends ParentTask
{
    /**
     * @param Vacancy $vacancy
     * @param list<int> $contactIds
     *
     * @return void
     */
    public function run(Vacancy $vacancy, array $contactIds): void
    {
        $vacancy->contacts()->sync($contactIds);
    }
}
