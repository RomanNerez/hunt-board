<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Tasks\CreateProfileProjectTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class CreateProfileProjectAction extends ParentAction
{
    /**
     * @param CreateProfileProjectTask $createProfileProjectTask
     */
    public function __construct(
        private readonly CreateProfileProjectTask $createProfileProjectTask,
    ) {
    }

    /**
     * @param int $workExperienceId
     * @param array<string, mixed> $data
     *
     * @return ProfileProject
     *
     * @throws \Throwable
     */
    public function run(int $workExperienceId, array $data): ProfileProject
    {
        return DB::transaction(fn (): ProfileProject => $this->createProfileProjectTask->run($workExperienceId, $data));
    }
}
