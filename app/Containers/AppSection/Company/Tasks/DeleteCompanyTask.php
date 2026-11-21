<?php

namespace App\Containers\AppSection\Company\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Company\Data\Repositories\CompanyRepository;
use App\Containers\AppSection\Company\Models\Company;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Prettus\Repository\Exceptions\RepositoryException;

final class DeleteCompanyTask extends ParentTask
{
    /**
     * @param CompanyRepository<Company> $repository
     */
    public function __construct(
        private readonly CompanyRepository $repository,
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
