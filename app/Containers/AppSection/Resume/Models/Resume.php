<?php

namespace App\Containers\AppSection\Resume\Models;

use App\Containers\AppSection\Resume\Data\Factories\ResumeFactory;
use App\Containers\AppSection\Resume\Enums\ResumeKind;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
