<?php

namespace App\Containers\AppSection\Resume\Models;

use App\Containers\AppSection\Resume\Data\Factories\ResumeLinkFactory;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $resume_id
 * @property string|null $icon
 * @property string $label
 * @property string $url
 * @property int $sort_order
 * @property bool $is_visible
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property Resume $resume
 *
 * @method static ResumeLinkFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class ResumeLink extends ParentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'resume_id',
        'icon',
        'label',
        'url',
        'sort_order',
        'is_visible',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
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
