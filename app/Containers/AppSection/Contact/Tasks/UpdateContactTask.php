<?php

namespace App\Containers\AppSection\Contact\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Contact\Data\Repositories\ContactRepository;
use App\Containers\AppSection\Contact\Models\Contact;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateContactTask extends ParentTask
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
     * @param array<string, mixed> $data
     *
     * @return Contact
     *
     * @throws ResourceNotFound
     */
    public function run(int $id, array $data): Contact
    {
        return $this->repository->update($data, $id);
    }
}
