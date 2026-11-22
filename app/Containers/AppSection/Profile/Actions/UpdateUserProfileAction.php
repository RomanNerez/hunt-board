<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Models\UserProfile;
use App\Containers\AppSection\Profile\Tasks\UpsertUserProfileTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class UpdateUserProfileAction extends ParentAction
{
    /**
     * @param UpsertUserProfileTask $upsertUserProfileTask
     */
    public function __construct(
        private readonly UpsertUserProfileTask $upsertUserProfileTask,
    ) {
    }

    /**
     * @param int $userId
     * @param array<string, mixed> $data
     *
     * @return UserProfile
     *
     * @throws \Throwable
     */
    public function run(int $userId, array $data): UserProfile
    {
        return DB::transaction(fn (): UserProfile => $this->upsertUserProfileTask->run($userId, $data));
    }
}
