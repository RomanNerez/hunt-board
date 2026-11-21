<?php

namespace App\Containers\AppSection\Company\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Company\Data\Repositories\CompanyTypeRepository;
use App\Containers\AppSection\Company\Models\CompanyType;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateCompanyTypeTask extends ParentTask
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
     * @param array<string, mixed> $data
     *
     * @return CompanyType
     *
     * @throws ResourceNotFound
     */
    public function run(int $id, array $data): CompanyType
    {
        return $this->repository->update($data, $id);
    }
}
