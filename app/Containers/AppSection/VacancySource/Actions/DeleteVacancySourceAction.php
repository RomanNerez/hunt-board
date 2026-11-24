<?php

namespace App\Containers\AppSection\VacancySource\Actions;

use App\Containers\AppSection\VacancySource\Tasks\DeleteVacancySourceTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class DeleteVacancySourceAction extends ParentAction
{
    /**
     * @param DeleteVacancySourceTask $deleteVacancySourceTask
     */
    public function __construct(
        private readonly DeleteVacancySourceTask $deleteVacancySourceTask,
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
        return DB::transaction(fn (): bool => $this->deleteVacancySourceTask->run($id));
    }
}
