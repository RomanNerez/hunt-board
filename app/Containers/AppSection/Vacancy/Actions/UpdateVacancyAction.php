<?php

namespace App\Containers\AppSection\Vacancy\Actions;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tasks\ReplaceVacancySourcesTask;
use App\Containers\AppSection\Vacancy\Tasks\SyncVacancyContactsTask;
use App\Containers\AppSection\Vacancy\Tasks\UpdateVacancyTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class UpdateVacancyAction extends ParentAction
{
    /**
     * @param UpdateVacancyTask $updateVacancyTask
     * @param ReplaceVacancySourcesTask $replaceVacancySourcesTask
     * @param SyncVacancyContactsTask $syncVacancyContactsTask
     */
    public function __construct(
        private readonly UpdateVacancyTask $updateVacancyTask,
        private readonly ReplaceVacancySourcesTask $replaceVacancySourcesTask,
        private readonly SyncVacancyContactsTask $syncVacancyContactsTask,
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
        $hasContacts = array_key_exists('contacts', $data);

        /** @var list<array<string, mixed>> $sources */
        $sources = Arr::pull($data, 'sources') ?? [];
        $contactIds = $this->contactIds(Arr::pull($data, 'contacts'));

        return DB::transaction(function () use ($id, $data, $hasSources, $sources, $hasContacts, $contactIds): Vacancy {
            $vacancy = $this->updateVacancyTask->run($id, $data);

            if ($hasSources) {
                $this->replaceVacancySourcesTask->run($vacancy, $sources);
            }

            if ($hasContacts) {
                $this->syncVacancyContactsTask->run($vacancy, $contactIds);
            }

            return $vacancy->load(Vacancy::DISPLAY_RELATIONS);
        });
    }

    /**
     * @param mixed $contacts
     *
     * @return list<int>
     */
    private function contactIds(mixed $contacts): array
    {
        $ids = [];

        foreach (is_array($contacts) ? $contacts : [] as $id) {
            if (is_numeric($id)) {
                $ids[] = (int) $id;
            }
        }

        return $ids;
    }
}
