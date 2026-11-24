<?php

namespace App\Containers\AppSection\Vacancy\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Vacancy\Data\Repositories\VacancyNoteRepository;
use App\Containers\AppSection\Vacancy\Models\VacancyNote;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Prettus\Repository\Exceptions\RepositoryException;

final class DeleteVacancyNoteTask extends ParentTask
{
    /**
     * @param VacancyNoteRepository<VacancyNote> $repository
     */
    public function __construct(
        private readonly VacancyNoteRepository $repository,
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
        return $this->repository->delete($id);
    }
}
