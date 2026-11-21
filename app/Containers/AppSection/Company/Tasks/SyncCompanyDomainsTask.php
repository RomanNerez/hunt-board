<?php

namespace App\Containers\AppSection\Company\Tasks;

use App\Containers\AppSection\Company\Models\Company;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class SyncCompanyDomainsTask extends ParentTask
{
    /**
     * @param Company $company
     * @param list<int> $domainIds
     *
     * @return void
     */
    public function run(Company $company, array $domainIds): void
    {
        $company->domains()->sync($domainIds);
    }
}
