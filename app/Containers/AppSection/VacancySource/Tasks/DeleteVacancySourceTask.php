<?php

namespace App\Containers\AppSection\VacancySource\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\VacancySource\Data\Repositories\VacancySourceRepository;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Prettus\Repository\Exceptions\RepositoryException;

final class DeleteVacancySourceTask extends ParentTask
{
    /**
     * @param VacancySourceRepository<VacancySource> $repository
     */
    public function __construct(
        private readonly VacancySourceRepository $repository,
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
