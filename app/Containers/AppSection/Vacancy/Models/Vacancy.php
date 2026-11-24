<?php

namespace App\Containers\AppSection\Vacancy\Models;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Contact\Models\Contact;
use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Vacancy\Data\Factories\VacancyFactory;
use App\Containers\AppSection\Vacancy\Enums\Currency;
use App\Containers\AppSection\Vacancy\Enums\WorkFormat;
use App\Containers\AppSection\Vacancy\Models\Concerns\SanitizesRichText;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $company_id
 * @property int $status_id
 * @property string $title
 * @property string|null $description
 * @property string|null $location
 * @property WorkFormat|null $work_format
 * @property int|null $salary_min
 * @property int|null $salary_max
 * @property Currency|null $currency
 * @property CarbonImmutable|null $applied_at
 * @property CarbonImmutable|null $next_action_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property Company $company
 * @property Status $status
 * @property Collection<int, VacancySource> $vacancySources
 * @property Collection<int, Contact> $contacts
 * @property Collection<int, VacancyNote> $notes
 *
 * @method static VacancyFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class Vacancy extends ParentModel
{
    use SanitizesRichText;

    /**
     * @var list<string>
     */
    public const DISPLAY_RELATIONS = ['company', 'status', 'vacancySources', 'contacts.vacancySources'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'status_id',
        'title',
        'description',
        'location',
        'work_format',
        'salary_min',
        'salary_max',
        'currency',
        'applied_at',
        'next_action_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'work_format' => WorkFormat::class,
        'currency' => Currency::class,
        'salary_min' => 'integer',
        'salary_max' => 'integer',
        'applied_at' => 'date',
        'next_action_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<Status, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

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
     * @return BelongsToMany<Contact, $this>
     */
    public function contacts(): BelongsToMany
    {
        $relation = $this->belongsToMany(Contact::class)->withTimestamps();
        $relation->getBaseQuery()->orderBy('contacts.name');

        return $relation;
    }

    /**
     * @return HasMany<VacancyNote, $this>
     */
    public function notes(): HasMany
    {
        return $this->hasMany(VacancyNote::class);
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function description(): Attribute
    {
        return Attribute::make(
            set: static fn (string|null $value): string|null => self::sanitizeRichText($value),
        );
    }
}
