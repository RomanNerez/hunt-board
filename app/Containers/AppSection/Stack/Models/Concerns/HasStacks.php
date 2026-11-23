<?php

namespace App\Containers\AppSection\Stack\Models\Concerns;

use App\Containers\AppSection\Stack\Models\Stack;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;

trait HasStacks
{
    /**
     * @return MorphToMany<Stack, $this>
     */
    public function stacks(): MorphToMany
    {
        return $this->morphToMany(Stack::class, 'stackable')
            ->withPivot('sort')
            ->orderByPivot('sort');
    }

    /**
     * @param Builder<static> $owners
     *
     * @return void
     */
    public static function detachStacksFrom(Builder $owners): void
    {
        $owner = new static();

        DB::table('stackables')
            ->where('stackable_type', $owner->getMorphClass())
            ->whereIn('stackable_id', $owners->select($owner->getQualifiedKeyName()))
            ->delete();
    }
}
