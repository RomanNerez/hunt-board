<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Tasks\DeleteProfileProjectTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class DeleteProfileProjectAction extends ParentAction
{
    /**
     * @param DeleteProfileProjectTask $deleteProfileProjectTask
     */
    public function __construct(
        private readonly DeleteProfileProjectTask $deleteProfileProjectTask,
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
        return DB::transaction(fn (): bool => $this->deleteProfileProjectTask->run($id));
    }
}
