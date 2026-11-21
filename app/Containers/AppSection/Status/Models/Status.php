<?php

namespace App\Containers\AppSection\Status\Models;

use App\Containers\AppSection\Status\Data\Factories\StatusFactory;
use App\Containers\AppSection\Status\Enums\StatusColor;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;

/**
 * @property int $id
 * @property string $name
 * @property StatusColor $color
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static StatusFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class Status extends ParentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'color',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'color' => StatusColor::class,
    ];
}
