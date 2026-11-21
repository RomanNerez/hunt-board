<?php

namespace App\Containers\AppSection\Status\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Status\Data\Repositories\StatusRepository;
use App\Containers\AppSection\Status\Models\Status;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateStatusTask extends ParentTask
{
    /**
     * @param StatusRepository<Status> $repository
     */
    public function __construct(
        private readonly StatusRepository $repository,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return Status
     *
     * @throws ResourceNotFound
     */
    public function run(int $id, array $data): Status
    {
        return $this->repository->update($data, $id);
    }
}
