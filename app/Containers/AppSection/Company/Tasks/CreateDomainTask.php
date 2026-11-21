<?php

namespace App\Containers\AppSection\Company\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Company\Data\Repositories\DomainRepository;
use App\Containers\AppSection\Company\Models\Domain;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateDomainTask extends ParentTask
{
    /**
     * @param DomainRepository<Domain> $repository
     */
    public function __construct(
        private readonly DomainRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Domain
     *
     * @throws ResourceCreationFailed
     */
    public function run(array $data): Domain
    {
        return $this->repository->create($data);
    }
}
