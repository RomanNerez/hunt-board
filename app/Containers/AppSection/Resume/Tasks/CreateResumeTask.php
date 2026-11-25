<?php

namespace App\Containers\AppSection\Resume\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Resume\Data\Repositories\ResumeRepository;
use App\Containers\AppSection\Resume\Enums\ResumeKind;
use App\Containers\AppSection\Resume\Enums\ResumeLocale;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Arr;

final class CreateResumeTask extends ParentTask
{
    /**
     * @param ResumeRepository<Resume> $repository
     */
    public function __construct(
        private readonly ResumeRepository $repository,
    ) {
    }

    /**
     * @param int $userId
     * @param array<string, mixed> $data
     *
     * @return Resume
     *
     * @throws ResourceCreationFailed
     */
    public function run(int $userId, array $data): Resume
    {
        return $this->repository->create([
            'kind' => ResumeKind::Master,
            'locale' => ResumeLocale::default(),
            ...Arr::only($data, ['kind', 'locale', 'first_name', 'last_name', 'headline', 'summary']),
            'user_id' => $userId,
        ]);
    }
}
