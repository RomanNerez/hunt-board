<?php

namespace App\Containers\AppSection\Company\Actions;

use App\Containers\AppSection\Company\Tasks\DeleteCompanyTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class DeleteCompanyAction extends ParentAction
{
    /**
     * @param DeleteCompanyTask $deleteCompanyTask
     */
    public function __construct(
        private readonly DeleteCompanyTask $deleteCompanyTask,
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
        return DB::transaction(fn (): bool => $this->deleteCompanyTask->run($id));
    }
}
