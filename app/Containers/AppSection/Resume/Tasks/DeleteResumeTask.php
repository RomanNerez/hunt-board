<?php

namespace App\Containers\AppSection\Resume\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Resume\Data\Repositories\ResumeRepository;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Prettus\Repository\Exceptions\RepositoryException;

final class DeleteResumeTask extends ParentTask
{
    /**
     * @param ResumeRepository<Resume> $repository
     */
    public function __construct(
        private readonly ResumeRepository $repository,
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
