<?php

namespace App\Containers\AppSection\Stack\Actions;

use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\Tasks\CreateStackTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class CreateStackAction extends ParentAction
{
    /**
     * @param CreateStackTask $createStackTask
     */
    public function __construct(
        private readonly CreateStackTask $createStackTask,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return Stack
     *
     * @throws \Throwable
     */
    public function run(array $data): Stack
    {
        return DB::transaction(fn (): Stack => $this->createStackTask->run($data));
    }
}
