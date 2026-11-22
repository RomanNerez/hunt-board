<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Models\ProfileEducation;
use App\Containers\AppSection\Profile\Tasks\ReplaceProfileEducationTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class SyncProfileEducationAction extends ParentAction
{
    /**
     * @param ReplaceProfileEducationTask $replaceProfileEducationTask
     */
    public function __construct(
        private readonly ReplaceProfileEducationTask $replaceProfileEducationTask,
    ) {
    }

    /**
     * @param int $userId
     * @param list<array<string, mixed>> $items
     *
     * @return Collection<int, ProfileEducation>
     *
     * @throws \Throwable
     */
    public function run(int $userId, array $items): Collection
    {
        return DB::transaction(fn (): Collection => $this->replaceProfileEducationTask->run($userId, $items));
    }
}
