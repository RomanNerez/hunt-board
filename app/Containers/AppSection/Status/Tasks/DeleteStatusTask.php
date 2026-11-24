<?php

namespace App\Containers\AppSection\Status\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Status\Data\Repositories\StatusRepository;
use App\Containers\AppSection\Status\Exceptions\StatusInUseException;
use App\Containers\AppSection\Status\Models\Status;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\QueryException;
use Prettus\Repository\Exceptions\RepositoryException;

final class DeleteStatusTask extends ParentTask
{
    private const FOREIGN_KEY_VIOLATION = '23503';

    /**
     * @param StatusRepository<Status> $repository
     */
    public function __construct(
        private readonly StatusRepository $repository,
    ) {
    }

    /**
     * @param int $id
     *
     * @return bool
     *
     * @throws ResourceNotFound
     * @throws RepositoryException
     * @throws StatusInUseException
     * @throws \Throwable
     */
    public function run(int $id): bool
    {
        try {
            return $this->repository->delete($id);
        } catch (\Throwable $exception) {
            if ($exception instanceof QueryException && self::FOREIGN_KEY_VIOLATION === (string) $exception->getCode()) {
                throw StatusInUseException::becauseReferenced($exception);
            }

            throw $exception;
        }
    }
}
