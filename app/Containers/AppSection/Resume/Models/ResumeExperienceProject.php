<?php

namespace App\Containers\AppSection\Resume\Models;

use App\Containers\AppSection\Resume\Data\Factories\ResumeExperienceProjectFactory;
use App\Containers\AppSection\Stack\Models\Concerns\HasStacks;
use App\Containers\AppSection\Stack\Models\Contracts\Stackable;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $resume_experience_id
 * @property string $name
 * @property string|null $role
 * @property string|null $description
 * @property int $sort_order
 * @property bool $is_visible
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property ResumeExperience $experience
 * @property Collection<int, Stack> $stacks
 *
 * @method static ResumeExperienceProjectFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class ResumeExperienceProject extends ParentModel implements Stackable
{
    use HasStacks;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'resume_experience_id',
        'name',
        'role',
        'description',
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
     * @return BelongsTo<ResumeExperience, $this>
     */
    public function experience(): BelongsTo
    {
        return $this->belongsTo(ResumeExperience::class, 'resume_experience_id');
    }
}
