<?php

namespace App\Containers\AppSection\Stack\Actions;

use App\Containers\AppSection\Stack\Tasks\DeleteStackTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class DeleteStackAction extends ParentAction
{
    /**
     * @param DeleteStackTask $deleteStackTask
     */
    public function __construct(
        private readonly DeleteStackTask $deleteStackTask,
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
        return DB::transaction(fn (): bool => $this->deleteStackTask->run($id));
    }
}
