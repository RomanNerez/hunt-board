<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Profile\Tasks\ReplaceProfileSkillGroupsTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class SyncProfileSkillGroupsAction extends ParentAction
{
    /**
     * @param ReplaceProfileSkillGroupsTask $replaceProfileSkillGroupsTask
     */
    public function __construct(
        private readonly ReplaceProfileSkillGroupsTask $replaceProfileSkillGroupsTask,
    ) {
    }

    /**
     * @param int $userId
     * @param list<array<string, mixed>> $items
     *
     * @return Collection<int, ProfileSkillGroup>
     *
     * @throws \Throwable
     */
    public function run(int $userId, array $items): Collection
    {
        return DB::transaction(fn (): Collection => $this->replaceProfileSkillGroupsTask->run($userId, $items));
    }
}
