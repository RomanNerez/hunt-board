<?php

namespace App\Containers\AppSection\Stack\Actions;

use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\Tasks\UpdateStackTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class UpdateStackAction extends ParentAction
{
    /**
     * @param UpdateStackTask $updateStackTask
     */
    public function __construct(
        private readonly UpdateStackTask $updateStackTask,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return Stack
     *
     * @throws \Throwable
     */
    public function run(int $id, array $data): Stack
    {
        return DB::transaction(fn (): Stack => $this->updateStackTask->run($id, $data));
    }
}
