<?php

namespace App\Containers\AppSection\Profile\Actions;

use App\Containers\AppSection\Profile\Models\ProfileLanguage;
use App\Containers\AppSection\Profile\Tasks\ReplaceProfileLanguagesTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class SyncProfileLanguagesAction extends ParentAction
{
    /**
     * @param ReplaceProfileLanguagesTask $replaceProfileLanguagesTask
     */
    public function __construct(
        private readonly ReplaceProfileLanguagesTask $replaceProfileLanguagesTask,
    ) {
    }

    /**
     * @param int $userId
     * @param list<array<string, mixed>> $items
     *
     * @return Collection<int, ProfileLanguage>
     *
     * @throws \Throwable
     */
    public function run(int $userId, array $items): Collection
    {
        return DB::transaction(fn (): Collection => $this->replaceProfileLanguagesTask->run($userId, $items));
    }
}
