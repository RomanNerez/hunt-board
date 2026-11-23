<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tasks\UpdateProfileWorkExperienceTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class UpdateProfileWorkExperienceAction extends ParentAction
{
    /**
     * @param UpdateProfileWorkExperienceTask $updateProfileWorkExperienceTask
     */
    public function __construct(
        private readonly UpdateProfileWorkExperienceTask $updateProfileWorkExperienceTask,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return ProfileWorkExperience
     *
     * @throws \Throwable
     */
    public function run(int $id, array $data): ProfileWorkExperience
    {
        return DB::transaction(fn (): ProfileWorkExperience => $this->updateProfileWorkExperienceTask->run($id, $data));
    }
}
