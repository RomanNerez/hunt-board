<?php

namespace App\Containers\AppSection\Company\Actions;

use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\Tasks\CreateDomainTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class CreateDomainAction extends ParentAction
{
    /**
     * @param CreateDomainTask $createDomainTask
     */
    public function __construct(
        private readonly CreateDomainTask $createDomainTask,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Domain
     *
     * @throws \Throwable
     */
    public function run(array $data): Domain
    {
        return DB::transaction(fn (): Domain => $this->createDomainTask->run($data));
    }
}
