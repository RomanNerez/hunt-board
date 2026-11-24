<?php

namespace App\Containers\AppSection\Vacancy\Actions;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tasks\UpdateVacancyTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class UpdateVacancyAction extends ParentAction
{
    /**
     * @param UpdateVacancyTask $updateVacancyTask
     */
    public function __construct(
        private readonly UpdateVacancyTask $updateVacancyTask,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return Vacancy
     *
     * @throws \Throwable
     */
    public function run(int $id, array $data): Vacancy
    {
        return DB::transaction(fn (): Vacancy => $this->updateVacancyTask->run($id, $data)->load(Vacancy::DISPLAY_RELATIONS));
    }
}
