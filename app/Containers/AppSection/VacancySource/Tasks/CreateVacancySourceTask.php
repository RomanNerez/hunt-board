<?php

namespace App\Containers\AppSection\VacancySource\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\VacancySource\Data\Repositories\VacancySourceRepository;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateVacancySourceTask extends ParentTask
{
    /**
     * @param VacancySourceRepository<VacancySource> $repository
     */
    public function __construct(
        private readonly VacancySourceRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return VacancySource
     *
     * @throws ResourceCreationFailed
     */
    public function run(array $data): VacancySource
    {
        return $this->repository->create($data);
    }
}
