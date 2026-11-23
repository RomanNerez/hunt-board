<?php

namespace App\Containers\AppSection\Profile\Tasks;

use Apiato\Core\Repositories\Exceptions\ResourceCreationFailed;
use App\Containers\AppSection\Profile\Data\Repositories\ProfileWorkExperienceRepository;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tasks\Concerns\NormalizesTranslations;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Arr;

final class CreateProfileWorkExperienceTask extends ParentTask
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
     * @param int $userId
     * @param array<string, mixed> $data
     *
     * @return ProfileWorkExperience
     *
     * @throws ResourceCreationFailed
     */
    public function run(int $userId, array $data): ProfileWorkExperience
    {
        /** @var array<string, mixed> $attributes */
        $attributes = Arr::only($data, self::ATTRIBUTES);

        return $this->repository->create([
            ...$this->normalizeTranslations($attributes, (new ProfileWorkExperience())->translatable),
            'user_id' => $userId,
        ]);
    }
}
