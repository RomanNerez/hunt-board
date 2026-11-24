<?php

namespace App\Containers\AppSection\Vacancy\Actions;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Vacancy\Models\VacancyNote;
use App\Containers\AppSection\Vacancy\Tasks\CreateVacancyNoteTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class CreateVacancyNoteAction extends ParentAction
{
    /**
     * @param CreateVacancyNoteTask $createVacancyNoteTask
     */
    public function __construct(
        private readonly CreateVacancyNoteTask $createVacancyNoteTask,
    ) {
    }

    /**
     * @param int $vacancyId
     * @param array<string, mixed> $data
     *
     * @return VacancyNote
     *
     * @throws ResourceCreationFailed
     */
    public function run(int $vacancyId, array $data): VacancyNote
    {
        return $this->createVacancyNoteTask->run($vacancyId, $data);
    }
}
