<?php

namespace App\Containers\AppSection\Status\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Status\Data\Repositories\StatusRepository;
use App\Containers\AppSection\Status\Models\Status;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Prettus\Repository\Exceptions\RepositoryException;

final class DeleteStatusTask extends ParentTask
{
    /**
     * @param StatusRepository<Status> $repository
     */
    public function __construct(
        private readonly StatusRepository $repository,
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
