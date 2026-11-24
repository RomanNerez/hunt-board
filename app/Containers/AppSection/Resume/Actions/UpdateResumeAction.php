<?php

namespace App\Containers\AppSection\Resume\Actions;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Tasks\UpdateResumeTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdateResumeAction extends ParentAction
{
    /**
     * @param UpdateResumeTask $updateResumeTask
     */
    public function __construct(
        private readonly UpdateResumeTask $updateResumeTask,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return Resume
     */
    public function run(int $id, array $data): Resume
    {
        return $this->updateResumeTask->run($id, $data);
    }
}
