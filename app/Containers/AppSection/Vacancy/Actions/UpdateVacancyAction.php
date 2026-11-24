<?php

namespace App\Containers\AppSection\Vacancy\Actions;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tasks\ReplaceVacancySourcesTask;
use App\Containers\AppSection\Vacancy\Tasks\UpdateVacancyTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class UpdateVacancyAction extends ParentAction
{
    /**
     * @param UpdateVacancyTask $updateVacancyTask
     * @param ReplaceVacancySourcesTask $replaceVacancySourcesTask
     */
    public function __construct(
        private readonly UpdateVacancyTask $updateVacancyTask,
        private readonly ReplaceVacancySourcesTask $replaceVacancySourcesTask,
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
        $hasSources = array_key_exists('sources', $data);

        /** @var list<array<string, mixed>> $sources */
        $sources = Arr::pull($data, 'sources') ?? [];

        return DB::transaction(function () use ($id, $data, $hasSources, $sources): Vacancy {
            $vacancy = $this->updateVacancyTask->run($id, $data);

            if ($hasSources) {
                $this->replaceVacancySourcesTask->run($vacancy, $sources);
            }

            return $vacancy->load(Vacancy::DISPLAY_RELATIONS);
        });
    }
}
