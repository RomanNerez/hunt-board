<?php

namespace App\Containers\AppSection\Resume\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Resume\Data\Repositories\ResumeRepository;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Arr;

final class UpdateResumeTask extends ParentTask
{
    /**
     * @param ResumeRepository<Resume> $repository
     */
    public function __construct(
        private readonly ResumeRepository $repository,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return Resume
     *
     * @throws ResourceNotFound
     */
    public function run(int $id, array $data): Resume
    {
        return $this->repository->update(Arr::only($data, ['kind', 'first_name', 'last_name', 'headline', 'summary']), $id);
    }
}
