<?php

namespace App\Containers\AppSection\Company\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Company\Data\Repositories\DomainRepository;
use App\Containers\AppSection\Company\Models\Domain;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Prettus\Repository\Exceptions\RepositoryException;

final class DeleteDomainTask extends ParentTask
{
    /**
     * @param DomainRepository<Domain> $repository
     */
    public function __construct(
        private readonly DomainRepository $repository,
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
