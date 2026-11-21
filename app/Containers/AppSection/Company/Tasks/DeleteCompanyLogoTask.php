<?php

namespace App\Containers\AppSection\Company\Tasks;

use App\Containers\AppSection\Company\Models\Company;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class DeleteCompanyLogoTask extends ParentTask
{
    /**
     * @param Company $company
     *
     * @return void
     */
    public function run(Company $company): void
    {
        $company->clearMediaCollection(Company::LOGO_COLLECTION);
    }
}
