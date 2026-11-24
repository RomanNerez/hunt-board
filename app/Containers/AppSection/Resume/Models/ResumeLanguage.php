<?php

namespace App\Containers\AppSection\Resume\Models;

use App\Containers\AppSection\Resume\Data\Factories\ResumeLanguageFactory;
use App\Containers\AppSection\Resume\Enums\ResumeLanguageLevel;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $resume_id
 * @property string $language
 * @property ResumeLanguageLevel|null $level
 * @property string|null $level_label
 * @property int $sort_order
 * @property bool $is_visible
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property Resume $resume
 *
 * @method static ResumeLanguageFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class ResumeLanguage extends ParentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'resume_id',
        'language',
        'level',
        'level_label',
        'sort_order',
        'is_visible',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'level' => ResumeLanguageLevel::class,
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
