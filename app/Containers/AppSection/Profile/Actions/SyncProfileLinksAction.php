<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Models\ProfileLink;
use App\Containers\AppSection\Profile\Tasks\ReplaceProfileLinksTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class SyncProfileLinksAction extends ParentAction
{
    /**
     * @param ReplaceProfileLinksTask $replaceProfileLinksTask
     */
    public function __construct(
        private readonly ReplaceProfileLinksTask $replaceProfileLinksTask,
    ) {
    }

    /**
     * @param int $userId
     * @param list<array<string, mixed>> $items
     *
     * @return Collection<int, ProfileLink>
     *
     * @throws \Throwable
     */
    public function run(int $userId, array $items): Collection
    {
        return DB::transaction(fn (): Collection => $this->replaceProfileLinksTask->run($userId, $items));
    }
}
