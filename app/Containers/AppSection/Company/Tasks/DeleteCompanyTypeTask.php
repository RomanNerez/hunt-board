<?php

namespace App\Containers\AppSection\Company\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Company\Data\Repositories\CompanyTypeRepository;
use App\Containers\AppSection\Company\Models\CompanyType;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Prettus\Repository\Exceptions\RepositoryException;

final class DeleteCompanyTypeTask extends ParentTask
{
    /**
     * @param CompanyTypeRepository<CompanyType> $repository
     */
    public function __construct(
        private readonly CompanyTypeRepository $repository,
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
