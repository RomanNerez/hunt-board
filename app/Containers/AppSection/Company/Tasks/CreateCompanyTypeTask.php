<?php

namespace App\Containers\AppSection\Company\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Company\Data\Repositories\CompanyTypeRepository;
use App\Containers\AppSection\Company\Models\CompanyType;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateCompanyTypeTask extends ParentTask
{
    /**
     * @param CompanyTypeRepository<CompanyType> $repository
     */
    public function __construct(
        private readonly CompanyTypeRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return CompanyType
     *
     * @throws ResourceCreationFailed
     */
    public function run(array $data): CompanyType
    {
        return $this->repository->create($data);
    }
}
