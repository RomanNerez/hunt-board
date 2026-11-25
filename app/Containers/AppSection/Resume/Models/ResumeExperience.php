<?php

namespace App\Containers\AppSection\Resume\Models;

use App\Containers\AppSection\Resume\Data\Factories\ResumeExperienceFactory;
use App\Containers\AppSection\Resume\Enums\ResumeEmploymentType;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $resume_id
 * @property string $company
 * @property string|null $company_url
 * @property string $position
 * @property string|null $location
 * @property ResumeEmploymentType|null $employment_type
 * @property CarbonImmutable $start_date
 * @property CarbonImmutable|null $end_date
 * @property int $sort_order
 * @property bool $is_visible
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property Resume $resume
 * @property Collection<int, ResumeExperienceProject> $projects
 *
 * @method static ResumeExperienceFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class ResumeExperience extends ParentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'resume_id',
        'company',
        'company_url',
        'position',
        'location',
        'employment_type',
        'start_date',
        'end_date',
        'sort_order',
        'is_visible',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'employment_type' => ResumeEmploymentType::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'sort_order' => 'integer',
        'is_visible' => 'boolean',
    ];

    /**
     * @return BelongsTo<Resume, $this>
     */
    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    /**
     * @return HasMany<ResumeExperienceProject, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(ResumeExperienceProject::class)->orderBy('sort_order');
    }
}
