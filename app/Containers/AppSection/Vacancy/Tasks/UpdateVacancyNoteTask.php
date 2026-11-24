<?php

namespace App\Containers\AppSection\Vacancy\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Vacancy\Data\Repositories\VacancyNoteRepository;
use App\Containers\AppSection\Vacancy\Models\VacancyNote;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Arr;

final class UpdateVacancyNoteTask extends ParentTask
{
    /**
     * @var list<string>
     */
    public const EDITABLE_ATTRIBUTES = ['title', 'body', 'occurred_at'];

    /**
     * @param VacancyNoteRepository<VacancyNote> $repository
     */
    public function __construct(
        private readonly VacancyNoteRepository $repository,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return VacancyNote
     *
     * @throws ResourceNotFound
     */
    public function run(int $id, array $data): VacancyNote
    {
        return $this->repository->update(Arr::only($data, self::EDITABLE_ATTRIBUTES), $id);
    }
}
