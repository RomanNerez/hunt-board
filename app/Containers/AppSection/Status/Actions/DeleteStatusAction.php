<?php

namespace App\Containers\AppSection\Status\Actions;

use App\Containers\AppSection\Status\Tasks\DeleteStatusTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class DeleteStatusAction extends ParentAction
{
    /**
     * @param DeleteStatusTask $deleteStatusTask
     */
    public function __construct(
        private readonly DeleteStatusTask $deleteStatusTask,
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
        return DB::transaction(fn (): bool => $this->deleteStatusTask->run($id));
    }
}
