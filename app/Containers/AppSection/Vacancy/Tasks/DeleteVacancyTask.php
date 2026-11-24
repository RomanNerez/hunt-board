<?php

namespace App\Containers\AppSection\Vacancy\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Vacancy\Data\Repositories\VacancyRepository;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Prettus\Repository\Exceptions\RepositoryException;

final class DeleteVacancyTask extends ParentTask
{
    /**
     * @param VacancyRepository<Vacancy> $repository
     */
    public function __construct(
        private readonly VacancyRepository $repository,
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
