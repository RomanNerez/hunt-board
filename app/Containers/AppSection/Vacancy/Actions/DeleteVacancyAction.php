<?php

namespace App\Containers\AppSection\Vacancy\Actions;

use App\Containers\AppSection\Vacancy\Tasks\DeleteVacancyTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class DeleteVacancyAction extends ParentAction
{
    /**
     * @param DeleteVacancyTask $deleteVacancyTask
     */
    public function __construct(
        private readonly DeleteVacancyTask $deleteVacancyTask,
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
        return DB::transaction(fn (): bool => $this->deleteVacancyTask->run($id));
    }
}
