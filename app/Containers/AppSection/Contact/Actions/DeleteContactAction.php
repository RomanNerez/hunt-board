<?php

namespace App\Containers\AppSection\Contact\Actions;

use App\Containers\AppSection\Contact\Tasks\DeleteContactTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class DeleteContactAction extends ParentAction
{
    /**
     * @param DeleteContactTask $deleteContactTask
     */
    public function __construct(
        private readonly DeleteContactTask $deleteContactTask,
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
        return DB::transaction(fn (): bool => $this->deleteContactTask->run($id));
    }
}
