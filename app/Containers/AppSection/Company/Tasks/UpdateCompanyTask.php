<?php

namespace App\Containers\AppSection\Company\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Company\Data\Repositories\CompanyRepository;
use App\Containers\AppSection\Company\Models\Company;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateCompanyTask extends ParentTask
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
     * @param array<string, mixed> $data
     *
     * @return Company
     *
     * @throws ResourceNotFound
     */
    public function run(int $id, array $data): Company
    {
        return $this->repository->update($data, $id);
    }
}
