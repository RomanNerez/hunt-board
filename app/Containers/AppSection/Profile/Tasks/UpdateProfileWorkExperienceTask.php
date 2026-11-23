<?php

namespace App\Containers\AppSection\Profile\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceNotFound;
use App\Containers\AppSection\Profile\Data\Repositories\ProfileWorkExperienceRepository;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tasks\Concerns\NormalizesTranslations;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Arr;

final class UpdateProfileWorkExperienceTask extends ParentTask
{
    use NormalizesTranslations;

    /**
     * @var list<string>
     */
    private const ATTRIBUTES = ['type', 'company_name', 'role', 'started_on', 'finished_on'];

    /**
     * @param ProfileWorkExperienceRepository<ProfileWorkExperience> $repository
     */
    public function __construct(
        private readonly ProfileWorkExperienceRepository $repository,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return ProfileWorkExperience
     *
     * @throws ResourceNotFound
     */
    public function run(int $id, array $data): ProfileWorkExperience
    {
        /** @var array<string, mixed> $attributes */
        $attributes = Arr::only($data, self::ATTRIBUTES);

        return $this->repository->update(
            $this->normalizeTranslations($attributes, (new ProfileWorkExperience())->translatable),
            $id,
        );
    }
}
