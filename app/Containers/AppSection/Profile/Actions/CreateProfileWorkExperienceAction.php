<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tasks\CreateProfileWorkExperienceTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class CreateProfileWorkExperienceAction extends ParentAction
{
    /**
     * @param CreateProfileWorkExperienceTask $createProfileWorkExperienceTask
     */
    public function __construct(
        private readonly CreateProfileWorkExperienceTask $createProfileWorkExperienceTask,
    ) {
    }

    /**
     * @param int $userId
     * @param array<string, mixed> $data
     *
     * @return ProfileWorkExperience
     *
     * @throws \Throwable
     */
    public function run(int $userId, array $data): ProfileWorkExperience
    {
        return DB::transaction(fn (): ProfileWorkExperience => $this->createProfileWorkExperienceTask->run($userId, $data));
    }
}
