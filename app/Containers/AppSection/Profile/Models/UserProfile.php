<?php

namespace App\Containers\AppSection\Profile\Models;

use App\Containers\AppSection\Profile\Data\Factories\UserProfileFactory;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $location
 * @property string|null $headline
 * @property string|null $summary
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property User $user
 * @property Collection<int, ProfileLink> $links
 * @property Collection<int, ProfileEducation> $educations
 * @property Collection<int, ProfileLanguage> $languages
 * @property Collection<int, ProfileSkillGroup> $skillGroups
 *
 * @method static UserProfileFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class UserProfile extends ParentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'location',
        'headline',
        'summary',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ProfileLink, $this>
     */
    public function links(): HasMany
    {
        return $this->hasMany(ProfileLink::class, 'user_id', 'user_id')->orderBy('sort');
    }

    /**
     * @return HasMany<ProfileEducation, $this>
     */
    public function educations(): HasMany
    {
        return $this->hasMany(ProfileEducation::class, 'user_id', 'user_id')->orderBy('sort');
    }

    /**
     * @return HasMany<ProfileLanguage, $this>
     */
    public function languages(): HasMany
    {
        return $this->hasMany(ProfileLanguage::class, 'user_id', 'user_id')->orderBy('sort');
    }

    /**
     * @return HasMany<ProfileSkillGroup, $this>
     */
    public function skillGroups(): HasMany
    {
        return $this->hasMany(ProfileSkillGroup::class, 'user_id', 'user_id')->orderBy('sort');
    }
}
