<?php

namespace App\Containers\AppSection\Company\Actions;

use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\Company\Tasks\UpdateCompanyTypeTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class UpdateCompanyTypeAction extends ParentAction
{
    /**
     * @param UpdateCompanyTypeTask $updateCompanyTypeTask
     */
    public function __construct(
        private readonly UpdateCompanyTypeTask $updateCompanyTypeTask,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return CompanyType
     *
     * @throws \Throwable
     */
    public function run(int $id, array $data): CompanyType
    {
        return DB::transaction(fn (): CompanyType => $this->updateCompanyTypeTask->run($id, $data));
    }
}
