<?php

namespace App\Containers\AppSection\Contact\Models;

use App\Containers\AppSection\Contact\Data\Factories\ContactFactory;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property Collection<int, VacancySource> $vacancySources
 * @property Collection<int, Vacancy> $vacancies
 *
 * @method static ContactFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class Contact extends ParentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * @return BelongsToMany<VacancySource, $this>
     */
    public function vacancySources(): BelongsToMany
    {
        return $this->belongsToMany(VacancySource::class)
            ->withPivot('url')
            ->withTimestamps()
            ->orderByPivot('id');
    }

    /**
     * @return BelongsToMany<Vacancy, $this>
     */
    public function vacancies(): BelongsToMany
    {
        return $this->belongsToMany(Vacancy::class)
            ->withTimestamps();
    }
}
