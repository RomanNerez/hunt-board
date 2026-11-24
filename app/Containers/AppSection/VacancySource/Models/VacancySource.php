<?php

namespace App\Containers\AppSection\VacancySource\Models;

use App\Containers\AppSection\VacancySource\Data\Factories\VacancySourceFactory;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;

/**
 * @property int $id
 * @property string $name
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static VacancySourceFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class VacancySource extends ParentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];
}
