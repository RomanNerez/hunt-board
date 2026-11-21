<?php

namespace App\Containers\AppSection\Status\Actions;

use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\Tasks\UpdateStatusTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class UpdateStatusAction extends ParentAction
{
    /**
     * @param UpdateStatusTask $updateStatusTask
     */
    public function __construct(
        private readonly UpdateStatusTask $updateStatusTask,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return Status
     *
     * @throws \Throwable
     */
    public function run(int $id, array $data): Status
    {
        return DB::transaction(fn (): Status => $this->updateStatusTask->run($id, $data));
    }
}
