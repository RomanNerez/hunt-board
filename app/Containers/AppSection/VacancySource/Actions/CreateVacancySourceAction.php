<?php

namespace App\Containers\AppSection\VacancySource\Actions;

use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\Tasks\CreateVacancySourceTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class CreateVacancySourceAction extends ParentAction
{
    /**
     * @param CreateVacancySourceTask $createVacancySourceTask
     */
    public function __construct(
        private readonly CreateVacancySourceTask $createVacancySourceTask,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return VacancySource
     *
     * @throws \Throwable
     */
    public function run(array $data): VacancySource
    {
        return DB::transaction(fn (): VacancySource => $this->createVacancySourceTask->run($data));
    }
}
