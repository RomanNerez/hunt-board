<?php

namespace App\Containers\AppSection\Profile\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Profile\Data\Repositories\ProfileProjectRepository;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Tasks\Concerns\PreparesProfileProjectData;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Validation\ValidationException;

final class CreateProfileProjectTask extends ParentTask
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
     * @param int $workExperienceId
     * @param array<string, mixed> $data
     *
     * @return ProfileProject
     *
     * @throws ValidationException
     * @throws ResourceCreationFailed
     */
    public function run(int $workExperienceId, array $data): ProfileProject
    {
        $attributes = $this->projectAttributes($data);

        $this->assertPeriodOrder($attributes['started_on'] ?? null, $attributes['finished_on'] ?? null);

        return $this->repository->create([
            'technologies' => [],
            ...$attributes,
            'work_experience_id' => $workExperienceId,
        ]);
    }
}
