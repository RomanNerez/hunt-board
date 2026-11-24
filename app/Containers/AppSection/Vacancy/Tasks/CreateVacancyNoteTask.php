<?php

namespace App\Containers\AppSection\Vacancy\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Vacancy\Data\Repositories\VacancyNoteRepository;
use App\Containers\AppSection\Vacancy\Models\VacancyNote;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Arr;

final class CreateVacancyNoteTask extends ParentTask
{
    /**
     * @param VacancyNoteRepository<VacancyNote> $repository
     */
    public function __construct(
        private readonly VacancyNoteRepository $repository,
    ) {
    }

    /**
     * @param int $vacancyId
     * @param array<string, mixed> $data
     *
     * @return VacancyNote
     *
     * @throws ResourceCreationFailed
     */
    public function run(int $vacancyId, array $data): VacancyNote
    {
        return $this->repository->create([
            ...Arr::only($data, UpdateVacancyNoteTask::EDITABLE_ATTRIBUTES),
            'vacancy_id' => $vacancyId,
        ]);
    }
}
