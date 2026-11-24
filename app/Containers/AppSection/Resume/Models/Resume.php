<?php

namespace App\Containers\AppSection\Resume\Models;

use App\Containers\AppSection\Resume\Data\Factories\ResumeFactory;
use App\Containers\AppSection\Resume\Enums\ResumeKind;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property ResumeKind $kind
 * @property string $first_name
 * @property string $last_name
 * @property string|null $headline
 * @property string|null $summary
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property User $user
 * @property Collection<int, ResumeLink> $links
 * @property Collection<int, ResumeExperience> $experiences
 * @property Collection<int, ResumeEducation> $educations
 *
 * @method static ResumeFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class Resume extends ParentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'kind',
        'first_name',
        'last_name',
        'headline',
        'summary',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'kind' => ResumeKind::class,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ResumeLink, $this>
     */
    public function links(): HasMany
    {
        return $this->hasMany(ResumeLink::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<ResumeExperience, $this>
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(ResumeExperience::class)->orderBy('sort_order');
    }

    /**
     * @return HasMany<ResumeEducation, $this>
     */
    public function educations(): HasMany
    {
        return $this->hasMany(ResumeEducation::class)->orderBy('sort_order');
    }
}
