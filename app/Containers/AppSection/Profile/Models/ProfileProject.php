<?php

namespace App\Containers\AppSection\Profile\Models;

use App\Containers\AppSection\Profile\Data\Factories\ProfileProjectFactory;
use App\Containers\AppSection\Profile\Models\Concerns\HasProfileTranslations;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $work_experience_id
 * @property string $name
 * @property CarbonImmutable $started_on
 * @property CarbonImmutable|null $finished_on
 * @property list<string> $technologies
 * @property string|null $description
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property ProfileWorkExperience $workExperience
 * @property array<string, array<string, string>> $translations
 *
 * @method static ProfileProjectFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class ProfileProject extends ParentModel
{
    use HasProfileTranslations;

    /**
     * @var list<string>
     */
    public array $translatable = ['name', 'description'];

    /**
     * @var string|null
     */
    protected $table = 'profile_projects';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'work_experience_id',
        'name',
        'started_on',
        'finished_on',
        'technologies',
        'description',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'started_on' => 'immutable_date',
        'finished_on' => 'immutable_date',
        'technologies' => 'array',
    ];

    /**
     * @return BelongsTo<ProfileWorkExperience, $this>
     */
    public function workExperience(): BelongsTo
    {
        return $this->belongsTo(ProfileWorkExperience::class, 'work_experience_id');
    }
}
