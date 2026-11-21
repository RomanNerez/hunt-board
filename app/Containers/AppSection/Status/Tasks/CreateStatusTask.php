<?php

namespace App\Containers\AppSection\Status\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Status\Data\Repositories\StatusRepository;
use App\Containers\AppSection\Status\Models\Status;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateStatusTask extends ParentTask
{
    /**
     * @param StatusRepository<Status> $repository
     */
    public function __construct(
        private readonly StatusRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Status
     *
     * @throws ResourceCreationFailed
     */
    public function run(array $data): Status
    {
        return $this->repository->create($data);
    }
}
