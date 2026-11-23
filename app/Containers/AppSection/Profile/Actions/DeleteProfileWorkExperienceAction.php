<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Tasks\DeleteProfileWorkExperienceTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class DeleteProfileWorkExperienceAction extends ParentAction
{
    /**
     * @param DeleteProfileWorkExperienceTask $deleteProfileWorkExperienceTask
     */
    public function __construct(
        private readonly DeleteProfileWorkExperienceTask $deleteProfileWorkExperienceTask,
    ) {
    }

    /**
     * @param int $id
     *
     * @return bool
     *
     * @throws \Throwable
     */
    public function run(int $id): bool
    {
        return DB::transaction(fn (): bool => $this->deleteProfileWorkExperienceTask->run($id));
    }
}
