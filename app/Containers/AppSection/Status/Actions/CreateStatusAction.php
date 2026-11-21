<?php

namespace App\Containers\AppSection\Status\Actions;

use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\Tasks\CreateStatusTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class CreateStatusAction extends ParentAction
{
    /**
     * @param CreateStatusTask $createStatusTask
     */
    public function __construct(
        private readonly CreateStatusTask $createStatusTask,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Status
     *
     * @throws \Throwable
     */
    public function run(array $data): Status
    {
        return DB::transaction(fn (): Status => $this->createStatusTask->run($data));
    }
}
