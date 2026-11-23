<?php

namespace App\Containers\AppSection\Profile\Models;

use App\Containers\AppSection\Profile\Data\Factories\ProfileSkillGroupFactory;
use App\Containers\AppSection\Profile\Models\Concerns\HasProfileTranslations;
use App\Containers\AppSection\Stack\Models\Concerns\HasStacks;
use App\Containers\AppSection\Stack\Models\Contracts\Stackable;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $sort
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property User $user
 * @property Collection<int, Stack> $stacks
 * @property array<string, array<string, string>> $translations
 *
 * @method static ProfileSkillGroupFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class ProfileSkillGroup extends ParentModel implements Stackable
{
    use HasProfileTranslations;
    use HasStacks;

    /**
     * @var list<string>
     */
    public array $translatable = ['name'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'sort',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
