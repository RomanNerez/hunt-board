<?php

namespace App\Containers\AppSection\Vacancy\Actions;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tasks\CreateVacancyTask;
use App\Containers\AppSection\Vacancy\Tasks\ReplaceVacancySourcesTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class CreateVacancyAction extends ParentAction
{
    /**
     * @param CreateVacancyTask $createVacancyTask
     * @param ReplaceVacancySourcesTask $replaceVacancySourcesTask
     */
    public function __construct(
        private readonly CreateVacancyTask $createVacancyTask,
        private readonly ReplaceVacancySourcesTask $replaceVacancySourcesTask,
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
        /** @var list<array<string, mixed>> $sources */
        $sources = Arr::pull($data, 'sources') ?? [];

        return DB::transaction(function () use ($data, $sources): Vacancy {
            $vacancy = $this->createVacancyTask->run($data);

            $this->replaceVacancySourcesTask->run($vacancy, $sources);

            return $vacancy->load(Vacancy::DISPLAY_RELATIONS);
        });
    }
}
