<?php

namespace App\Containers\AppSection\Company\Actions;

use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\Tasks\UpdateDomainTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class UpdateDomainAction extends ParentAction
{
    /**
     * @param UpdateDomainTask $updateDomainTask
     */
    public function __construct(
        private readonly UpdateDomainTask $updateDomainTask,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return Domain
     *
     * @throws \Throwable
     */
    public function run(int $id, array $data): Domain
    {
        return DB::transaction(fn (): Domain => $this->updateDomainTask->run($id, $data));
    }
}
