<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Profile\Tasks\ReplaceProfileSkillGroupsTask;
use App\Containers\AppSection\Stack\Tasks\SyncStacksTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class SyncProfileSkillGroupsAction extends ParentAction
{
    /**
     * @param ReplaceProfileSkillGroupsTask $replaceProfileSkillGroupsTask
     * @param SyncStacksTask $syncStacksTask
     */
    public function __construct(
        private readonly ReplaceProfileSkillGroupsTask $replaceProfileSkillGroupsTask,
        private readonly SyncStacksTask $syncStacksTask,
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
        return DB::transaction(function () use ($userId, $items): Collection {
            $groups = $this->replaceProfileSkillGroupsTask->run($userId, $items);

            foreach ($groups as $index => $group) {
                $this->syncStacksTask->run($group, $items[$index]['stacks'] ?? []);
            }

            return $groups;
        });
    }
}
