<?php

namespace App\Containers\AppSection\Vacancy\Models;

use App\Containers\AppSection\Vacancy\Data\Factories\VacancyNoteFactory;
use App\Containers\AppSection\Vacancy\Models\Concerns\SanitizesRichText;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $vacancy_id
 * @property string $title
 * @property string $body
 * @property CarbonImmutable $occurred_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property Vacancy $vacancy
 *
 * @method static VacancyNoteFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class VacancyNote extends ParentModel
{
    use SanitizesRichText;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'vacancy_id',
        'title',
        'body',
        'occurred_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<Vacancy, $this>
     */
    public function vacancy(): BelongsTo
    {
        return $this->belongsTo(Vacancy::class);
    }

    /**
     * @return Attribute<string|null, string|null>
     */
    protected function body(): Attribute
    {
        return Attribute::make(
            set: static fn (string|null $value): string|null => self::sanitizeRichText($value),
        );
    }
}
