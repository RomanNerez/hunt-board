<?php

namespace App\Containers\AppSection\Resume\Actions;

use App\Containers\AppSection\Resume\Tasks\DeleteResumeTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class DeleteResumeAction extends ParentAction
{
    /**
     * @param DeleteResumeTask $deleteResumeTask
     */
    public function __construct(
        private readonly DeleteResumeTask $deleteResumeTask,
    ) {
    }

    /**
     * @param int $id
     *
     * @return bool
     *
     * @throws \Throwable
     */
    public function run(int $id): bool
    {
        return DB::transaction(fn (): bool => $this->deleteResumeTask->run($id));
    }
}
