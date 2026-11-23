<?php

namespace App\Containers\AppSection\Stack\Tasks;

use App\Containers\AppSection\Stack\Models\Contracts\Stackable;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class SyncStacksTask extends ParentTask
{
    /**
     * @param Stackable $owner
     * @param mixed $stackIds
     *
     * @return void
     */
    public function run(Stackable $owner, mixed $stackIds): void
    {
        $attachments = [];

        foreach ($this->uniqueIds($stackIds) as $sort => $id) {
            $attachments[$id] = ['sort' => $sort];
        }

        $owner->stacks()->sync($attachments);
    }

    /**
     * @param mixed $stackIds
     *
     * @return list<int>
     */
    private function uniqueIds(mixed $stackIds): array
    {
        $ids = [];

        foreach (is_array($stackIds) ? $stackIds : [] as $value) {
            if (is_int($value) || (is_string($value) && ctype_digit($value))) {
                $ids[] = (int) $value;
            }
        }

        return array_values(array_unique($ids));
    }
}
