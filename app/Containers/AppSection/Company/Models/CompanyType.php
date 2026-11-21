<?php

namespace App\Containers\AppSection\Company\Models;

use App\Containers\AppSection\Company\Data\Factories\CompanyTypeFactory;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static CompanyTypeFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class CompanyType extends ParentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<Company, $this>
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }
}
