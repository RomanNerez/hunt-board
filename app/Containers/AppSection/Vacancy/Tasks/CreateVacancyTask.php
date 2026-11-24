<?php

namespace App\Containers\AppSection\Vacancy\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Vacancy\Data\Repositories\VacancyRepository;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateVacancyTask extends ParentTask
{
    /**
     * @param VacancyRepository<Vacancy> $repository
     */
    public function __construct(
        private readonly VacancyRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Vacancy
     *
     * @throws ResourceCreationFailed
     */
    public function run(array $data): Vacancy
    {
        return $this->repository->create($data);
    }
}
