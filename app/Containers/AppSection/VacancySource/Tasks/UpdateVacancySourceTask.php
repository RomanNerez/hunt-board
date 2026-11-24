<?php

namespace App\Containers\AppSection\VacancySource\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\VacancySource\Data\Repositories\VacancySourceRepository;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateVacancySourceTask extends ParentTask
{
    /**
     * @param VacancySourceRepository<VacancySource> $repository
     */
    public function __construct(
        private readonly VacancySourceRepository $repository,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return VacancySource
     *
     * @throws ResourceNotFound
     */
    public function run(int $id, array $data): VacancySource
    {
        return $this->repository->update($data, $id);
    }
}
