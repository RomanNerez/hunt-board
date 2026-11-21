<?php

namespace App\Containers\AppSection\Company\Models;

use App\Containers\AppSection\Company\Data\Factories\CompanyFactory;
use App\Ship\Parents\Models\Model as ParentModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $company_type_id
 * @property string|null $website_url
 * @property string|null $vacancies_url
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property CompanyType|null $companyType
 * @property Collection<int, Domain> $domains
 *
 * @method static CompanyFactory<self> factory(int|null $count = null, array<string, mixed> $state = [])
 */
final class Company extends ParentModel implements HasMedia
{
    use InteractsWithMedia;

    public const LOGO_COLLECTION = 'logo';

    public const LOGO_THUMB_CONVERSION = 'thumb';

    public const LOGO_THUMB_SIZE = 128;

    /**
     * @var list<string>
     */
    public const LOGO_MIME_TYPES = [
        'image/png',
        'image/jpeg',
        'image/webp',
        'image/svg+xml',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'company_type_id',
        'website_url',
        'vacancies_url',
    ];

    /**
     * @return BelongsTo<CompanyType, $this>
     */
    public function companyType(): BelongsTo
    {
        return $this->belongsTo(CompanyType::class);
    }

    /**
     * @return BelongsToMany<Domain, $this>
     */
    public function domains(): BelongsToMany
    {
        return $this->belongsToMany(Domain::class, 'company_domain');
    }

    /**
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::LOGO_COLLECTION)
            ->singleFile()
            ->useDisk('public')
            ->acceptsMimeTypes(self::LOGO_MIME_TYPES);
    }

    /**
     * @param Media|null $media
     *
     * @return void
     */
    public function registerMediaConversions(Media|null $media = null): void
    {
        if ('image/svg+xml' === $media?->mime_type) {
            return;
        }

        $this->addMediaConversion(self::LOGO_THUMB_CONVERSION)
            ->performOnCollections(self::LOGO_COLLECTION)
            ->nonQueued()
            ->fit(Fit::Max, self::LOGO_THUMB_SIZE, self::LOGO_THUMB_SIZE)
            ->format('webp');
    }

    /**
     * @return string|null
     */
    public function logoThumbPath(): string|null
    {
        $logo = $this->getFirstMedia(self::LOGO_COLLECTION);

        if (null === $logo) {
            return null;
        }

        return $logo->hasGeneratedConversion(self::LOGO_THUMB_CONVERSION)
            ? $logo->getPathRelativeToRoot(self::LOGO_THUMB_CONVERSION)
            : $logo->getPathRelativeToRoot();
    }
}
