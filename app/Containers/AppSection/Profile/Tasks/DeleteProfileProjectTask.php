<?php

namespace App\Containers\AppSection\Profile\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Profile\Data\Repositories\ProfileProjectRepository;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Prettus\Repository\Exceptions\RepositoryException;

final class DeleteProfileProjectTask extends ParentTask
{
    /**
     * @param ProfileProjectRepository<ProfileProject> $repository
     */
    public function __construct(
        private readonly ProfileProjectRepository $repository,
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
