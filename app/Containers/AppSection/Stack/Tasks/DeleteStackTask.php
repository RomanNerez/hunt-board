<?php

namespace App\Containers\AppSection\Stack\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Stack\Data\Repositories\StackRepository;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Prettus\Repository\Exceptions\RepositoryException;

final class DeleteStackTask extends ParentTask
{
    /**
     * @param StackRepository<Stack> $repository
     */
    public function __construct(
        private readonly StackRepository $repository,
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
