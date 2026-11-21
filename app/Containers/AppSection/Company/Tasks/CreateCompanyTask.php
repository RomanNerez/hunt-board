<?php

namespace App\Containers\AppSection\Company\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Company\Data\Repositories\CompanyRepository;
use App\Containers\AppSection\Company\Models\Company;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateCompanyTask extends ParentTask
{
    /**
     * @param CompanyRepository<Company> $repository
     */
    public function __construct(
        private readonly CompanyRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Company
     *
     * @throws ResourceCreationFailed
     */
    public function run(array $data): Company
    {
        return $this->repository->create($data);
    }
}
