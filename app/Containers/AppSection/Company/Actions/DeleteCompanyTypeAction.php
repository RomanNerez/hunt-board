<?php

namespace App\Containers\AppSection\Company\Actions;

use App\Containers\AppSection\Company\Tasks\DeleteCompanyTypeTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class DeleteCompanyTypeAction extends ParentAction
{
    /**
     * @param DeleteCompanyTypeTask $deleteCompanyTypeTask
     */
    public function __construct(
        private readonly DeleteCompanyTypeTask $deleteCompanyTypeTask,
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
        return DB::transaction(fn (): bool => $this->deleteCompanyTypeTask->run($id));
    }
}
