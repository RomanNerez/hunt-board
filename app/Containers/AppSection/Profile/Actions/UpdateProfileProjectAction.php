<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Tasks\UpdateProfileProjectTask;
use App\Containers\AppSection\Stack\Tasks\SyncStacksTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class UpdateProfileProjectAction extends ParentAction
{
    /**
     * @param UpdateProfileProjectTask $updateProfileProjectTask
     * @param SyncStacksTask $syncStacksTask
     */
    public function __construct(
        private readonly UpdateProfileProjectTask $updateProfileProjectTask,
        private readonly SyncStacksTask $syncStacksTask,
    ) {
    }

    /**
     * @param int $id
     * @param array<string, mixed> $data
     *
     * @return ProfileProject
     *
     * @throws \Throwable
     */
    public function run(int $id, array $data): ProfileProject
    {
        return DB::transaction(function () use ($id, $data): ProfileProject {
            $project = $this->updateProfileProjectTask->run($id, $data);

            if (array_key_exists('stacks', $data)) {
                $this->syncStacksTask->run($project, $data['stacks']);
            }

            return $project;
        });
    }
}
