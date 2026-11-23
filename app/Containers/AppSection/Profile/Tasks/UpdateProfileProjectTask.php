<?php

namespace App\Containers\AppSection\Profile\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Profile\Data\Repositories\ProfileProjectRepository;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Tasks\Concerns\PreparesProfileProjectData;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Validation\ValidationException;

final class UpdateProfileProjectTask extends ParentTask
{
    use PreparesProfileProjectData;

    /**
     * @param ProfileProjectRepository<ProfileProject> $repository
     */
    public function __construct(
        private readonly ProfileProjectRepository $repository,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return ProfileProject
     *
     * @throws ValidationException
     * @throws ResourceNotFound
     */
    public function run(int $id, array $data): ProfileProject
    {
        $attributes = $this->projectAttributes($data);

        /** @var ProfileProject $project */
        $project = $this->repository->find($id);

        $this->assertPeriodOrder(
            array_key_exists('started_on', $attributes) ? $attributes['started_on'] : $project->started_on,
            array_key_exists('finished_on', $attributes) ? $attributes['finished_on'] : $project->finished_on,
        );

        return $this->repository->update($attributes, $id);
    }
}
