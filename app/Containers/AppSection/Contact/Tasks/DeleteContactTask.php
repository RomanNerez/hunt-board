<?php

namespace App\Containers\AppSection\Contact\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Contact\Data\Repositories\ContactRepository;
use App\Containers\AppSection\Contact\Models\Contact;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Prettus\Repository\Exceptions\RepositoryException;

final class DeleteContactTask extends ParentTask
{
    /**
     * @param ContactRepository<Contact> $repository
     */
    public function __construct(
        private readonly ContactRepository $repository,
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
