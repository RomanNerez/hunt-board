<?php

namespace App\Containers\AppSection\Company\Actions;

use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\Company\Tasks\CreateCompanyTypeTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class CreateCompanyTypeAction extends ParentAction
{
    /**
     * @param CreateCompanyTypeTask $createCompanyTypeTask
     */
    public function __construct(
        private readonly CreateCompanyTypeTask $createCompanyTypeTask,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return CompanyType
     *
     * @throws \Throwable
     */
    public function run(array $data): CompanyType
    {
        return DB::transaction(fn (): CompanyType => $this->createCompanyTypeTask->run($data));
    }
}
