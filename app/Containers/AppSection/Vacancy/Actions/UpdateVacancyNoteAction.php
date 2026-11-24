<?php

namespace App\Containers\AppSection\Vacancy\Actions;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Vacancy\Models\VacancyNote;
use App\Containers\AppSection\Vacancy\Tasks\UpdateVacancyNoteTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdateVacancyNoteAction extends ParentAction
{
    /**
     * @param UpdateVacancyNoteTask $updateVacancyNoteTask
     */
    public function __construct(
        private readonly UpdateVacancyNoteTask $updateVacancyNoteTask,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return VacancyNote
     *
     * @throws ResourceNotFound
     */
    public function run(int $id, array $data): VacancyNote
    {
        return $this->updateVacancyNoteTask->run($id, $data);
    }
}
