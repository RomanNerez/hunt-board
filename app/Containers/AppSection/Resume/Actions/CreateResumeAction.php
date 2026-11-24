<?php

namespace App\Containers\AppSection\Resume\Actions;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Tasks\CreateResumeTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class CreateResumeAction extends ParentAction
{
    /**
     * @param CreateResumeTask $createResumeTask
     */
    public function __construct(
        private readonly CreateResumeTask $createResumeTask,
    ) {
    }

    /**
     * @param int $userId
     * @param array<string, mixed> $data
     *
     * @return Resume
     */
    public function run(int $userId, array $data): Resume
    {
        return $this->createResumeTask->run($userId, $data);
    }
}
