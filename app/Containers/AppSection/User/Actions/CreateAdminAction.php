<?php

namespace App\Containers\AppSection\User\Actions;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\User\Tasks\CreateUserTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class CreateAdminAction extends ParentAction
{
    public function __construct(
        private readonly CreateUserTask $createUserTask,
    ) {
    }

    public function run(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = $this->createUserTask->run($data);
            $user->markEmailAsVerified();

            return $user;
        });
    }
}
