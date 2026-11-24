<?php

namespace App\Containers\AppSection\Vacancy\Actions;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Vacancy\Tasks\DeleteVacancyNoteTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Prettus\Repository\Exceptions\RepositoryException;

final class DeleteVacancyNoteAction extends ParentAction
{
    /**
     * @param DeleteVacancyNoteTask $deleteVacancyNoteTask
     */
    public function __construct(
        private readonly DeleteVacancyNoteTask $deleteVacancyNoteTask,
    ) {
    }

    /**
     * @param int $id
     *
     * @return bool
     *
     * @throws ResourceNotFound
     * @throws RepositoryException
     */
    public function run(int $id): bool
    {
        return $this->deleteVacancyNoteTask->run($id);
    }
}
