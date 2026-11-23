<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Tasks\UpdateProfileProjectTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class UpdateProfileProjectAction extends ParentAction
{
    /**
     * @param UpdateProfileProjectTask $updateProfileProjectTask
     */
    public function __construct(
        private readonly UpdateProfileProjectTask $updateProfileProjectTask,
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
        return DB::transaction(fn (): ProfileProject => $this->updateProfileProjectTask->run($id, $data));
    }
}
