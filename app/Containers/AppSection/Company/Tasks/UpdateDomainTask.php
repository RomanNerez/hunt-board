<?php

namespace App\Containers\AppSection\Company\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Company\Data\Repositories\DomainRepository;
use App\Containers\AppSection\Company\Models\Domain;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateDomainTask extends ParentTask
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
     * @param array<string, mixed> $data
     *
     * @return Domain
     *
     * @throws ResourceNotFound
     */
    public function run(int $id, array $data): Domain
    {
        return $this->repository->update($data, $id);
    }
}
