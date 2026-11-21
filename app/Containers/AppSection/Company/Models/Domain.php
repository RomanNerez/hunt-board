<?php

namespace App\Containers\AppSection\Company\Models;

use App\Containers\AppSection\Company\Data\Factories\DomainFactory;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 *
 * @method static DomainFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class Domain extends ParentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * @return BelongsToMany<Company, $this>
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_domain');
    }
}
