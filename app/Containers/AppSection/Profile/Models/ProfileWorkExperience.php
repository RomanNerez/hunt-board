<?php

namespace App\Containers\AppSection\Profile\Models;

use App\Containers\AppSection\Profile\Data\Factories\ProfileWorkExperienceFactory;
use App\Containers\AppSection\Profile\Enums\WorkExperienceType;
use App\Containers\AppSection\Profile\Models\Concerns\HasProfileTranslations;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property WorkExperienceType $type
 * @property string $company_name
 * @property string $role
 * @property CarbonImmutable $started_on
 * @property CarbonImmutable|null $finished_on
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property User $user
 * @property Collection<int, ProfileProject> $projects
 * @property array<string, array<string, string>> $translations
 *
 * @method static ProfileWorkExperienceFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class ProfileWorkExperience extends ParentModel
{
    use HasProfileTranslations;

    /**
     * @var list<string>
     */
    public array $translatable = ['company_name', 'role'];

    /**
     * @var string|null
     */
    protected $table = 'profile_work_experiences';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'company_name',
        'role',
        'started_on',
        'finished_on',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'type' => WorkExperienceType::class,
        'started_on' => 'immutable_date',
        'finished_on' => 'immutable_date',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ProfileProject, $this>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(ProfileProject::class, 'work_experience_id')
            ->orderByDesc('started_on')
            ->orderByDesc('id');
    }
}
