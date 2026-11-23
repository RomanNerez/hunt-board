<?php

namespace App\Containers\AppSection\Profile\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Profile\Data\Repositories\ProfileWorkExperienceRepository;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Prettus\Repository\Exceptions\RepositoryException;

final class DeleteProfileWorkExperienceTask extends ParentTask
{
    /**
     * @param ProfileWorkExperienceRepository<ProfileWorkExperience> $repository
     */
    public function __construct(
        private readonly ProfileWorkExperienceRepository $repository,
    ) {
    }

    /**
     * @param int $id
     *
     * @return bool
     *
     * @throws ResourceNotFound
     * @throws RepositoryException
     */
    public function run(int $id): bool
    {
        ProfileProject::detachStacksFrom(ProfileProject::query()->where('work_experience_id', $id));

        return $this->repository->delete($id);
    }
}
