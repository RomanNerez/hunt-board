<?php

namespace App\Containers\AppSection\Profile\Models;

use App\Containers\AppSection\Profile\Data\Factories\ProfileSkillGroupFactory;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property list<string> $skills
 * @property int $sort
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property User $user
 *
 * @method static ProfileSkillGroupFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class ProfileSkillGroup extends ParentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'skills',
        'sort',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'skills' => 'array',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
