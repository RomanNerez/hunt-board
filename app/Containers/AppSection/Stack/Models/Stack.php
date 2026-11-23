<?php

namespace App\Containers\AppSection\Stack\Models;

use App\Containers\AppSection\Stack\Data\Factories\StackFactory;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;

/**
 * @property int $id
 * @property string $name
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static StackFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class Stack extends ParentModel
{
    public const NAME_MAX_LENGTH = 100;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * @param string $name
     * @param int|null $ignoredId
     *
     * @return bool
     */
    public static function nameIsTaken(string $name, int|null $ignoredId = null): bool
    {
        return self::query()
            ->whereRaw('lower(name) = lower(?)', [trim($name)])
            ->when(null !== $ignoredId, static fn ($query) => $query->whereKeyNot($ignoredId))
            ->exists();
    }
}
