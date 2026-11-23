<?php

namespace App\Containers\AppSection\Stack\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Stack\Data\Repositories\StackRepository;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\Tasks\Concerns\ValidatesStackName;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Validation\ValidationException;

final class UpdateStackTask extends ParentTask
{
    use ValidatesStackName;

    /**
     * @param StackRepository<Stack> $repository
     */
    public function __construct(
        private readonly StackRepository $repository,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return Stack
     *
     * @throws ValidationException
     * @throws ResourceNotFound
     */
    public function run(int $id, array $data): Stack
    {
        return $this->repository->update([
            'name' => $this->validatedStackName($data['name'] ?? null, $id),
        ], $id);
    }
}
