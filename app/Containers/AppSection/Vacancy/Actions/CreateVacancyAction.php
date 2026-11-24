<?php

namespace App\Containers\AppSection\Vacancy\Actions;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tasks\CreateVacancyTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class CreateVacancyAction extends ParentAction
{
    /**
     * @param CreateVacancyTask $createVacancyTask
     */
    public function __construct(
        private readonly CreateVacancyTask $createVacancyTask,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Vacancy
     *
     * @throws \Throwable
     */
    public function run(array $data): Vacancy
    {
        return DB::transaction(fn (): Vacancy => $this->createVacancyTask->run($data)->load(Vacancy::DISPLAY_RELATIONS));
    }
}
