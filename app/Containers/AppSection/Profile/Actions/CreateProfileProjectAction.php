<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Tasks\CreateProfileProjectTask;
use App\Containers\AppSection\Stack\Tasks\SyncStacksTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class CreateProfileProjectAction extends ParentAction
{
    /**
     * @param CreateProfileProjectTask $createProfileProjectTask
     * @param SyncStacksTask $syncStacksTask
     */
    public function __construct(
        private readonly CreateProfileProjectTask $createProfileProjectTask,
        private readonly SyncStacksTask $syncStacksTask,
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
        return DB::transaction(function () use ($workExperienceId, $data): ProfileProject {
            $project = $this->createProfileProjectTask->run($workExperienceId, $data);

            if (array_key_exists('stacks', $data)) {
                $this->syncStacksTask->run($project, $data['stacks']);
            }

            return $project;
        });
    }
}
