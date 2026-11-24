<?php

namespace App\Containers\AppSection\Vacancy\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Vacancy\Data\Repositories\VacancyRepository;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateVacancyTask extends ParentTask
{
    /**
     * @param VacancyRepository<Vacancy> $repository
     */
    public function __construct(
        private readonly VacancyRepository $repository,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return Vacancy
     *
     * @throws ResourceNotFound
     */
    public function run(int $id, array $data): Vacancy
    {
        return $this->repository->update($data, $id);
    }
}
