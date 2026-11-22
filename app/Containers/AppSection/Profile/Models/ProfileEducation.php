<?php

namespace App\Containers\AppSection\Profile\Models;

use App\Containers\AppSection\Profile\Data\Factories\ProfileEducationFactory;
use App\Containers\AppSection\Profile\Enums\EducationType;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property EducationType $type
 * @property string $title
 * @property string|null $institution
 * @property CarbonImmutable|null $started_on
 * @property CarbonImmutable|null $finished_on
 * @property string|null $url
 * @property string|null $description
 * @property int $sort
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property User $user
 *
 * @method static ProfileEducationFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class ProfileEducation extends ParentModel
{
    /**
     * @var string|null
     */
    protected $table = 'profile_educations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'institution',
        'started_on',
        'finished_on',
        'url',
        'description',
        'sort',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'type' => EducationType::class,
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
}
