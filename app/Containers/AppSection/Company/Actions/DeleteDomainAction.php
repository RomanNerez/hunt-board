<?php

namespace App\Containers\AppSection\Company\Actions;

use App\Containers\AppSection\Company\Tasks\DeleteDomainTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class DeleteDomainAction extends ParentAction
{
    /**
     * @param DeleteDomainTask $deleteDomainTask
     */
    public function __construct(
        private readonly DeleteDomainTask $deleteDomainTask,
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
        return DB::transaction(fn (): bool => $this->deleteDomainTask->run($id));
    }
}
