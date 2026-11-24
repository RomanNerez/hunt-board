<?php

namespace App\Containers\AppSection\Contact\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Contact\Data\Repositories\ContactRepository;
use App\Containers\AppSection\Contact\Models\Contact;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateContactTask extends ParentTask
{
    /**
     * @param ContactRepository<Contact> $repository
     */
    public function __construct(
        private readonly ContactRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Contact
     *
     * @throws ResourceCreationFailed
     */
    public function run(array $data): Contact
    {
        return $this->repository->create($data);
    }
}
