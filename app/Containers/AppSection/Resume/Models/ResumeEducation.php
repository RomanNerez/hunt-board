<?php

namespace App\Containers\AppSection\Resume\Models;

use App\Containers\AppSection\Resume\Data\Factories\ResumeEducationFactory;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $resume_id
 * @property string $institution
 * @property string|null $institution_url
 * @property string|null $degree
 * @property string|null $field_of_study
 * @property string|null $location
 * @property CarbonImmutable|null $start_date
 * @property CarbonImmutable|null $end_date
 * @property string|null $description
 * @property int $sort_order
 * @property bool $is_visible
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property Resume $resume
 *
 * @method static ResumeEducationFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class ResumeEducation extends ParentModel
{
    /**
     * @var string|null
     */
    protected $table = 'resume_educations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'resume_id',
        'institution',
        'institution_url',
        'degree',
        'field_of_study',
        'location',
        'start_date',
        'end_date',
        'description',
        'sort_order',
        'is_visible',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
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
}
