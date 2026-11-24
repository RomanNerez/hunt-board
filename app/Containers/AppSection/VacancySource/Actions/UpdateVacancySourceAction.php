<?php

namespace App\Containers\AppSection\VacancySource\Actions;

use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\Tasks\UpdateVacancySourceTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class UpdateVacancySourceAction extends ParentAction
{
    /**
     * @param UpdateVacancySourceTask $updateVacancySourceTask
     */
    public function __construct(
        private readonly UpdateVacancySourceTask $updateVacancySourceTask,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return VacancySource
     *
     * @throws \Throwable
     */
    public function run(int $id, array $data): VacancySource
    {
        return DB::transaction(fn (): VacancySource => $this->updateVacancySourceTask->run($id, $data));
    }
}
