<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Models;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection;

use function Safe\getimagesize;

#[CoversClass(Company::class)]
final class CompanyTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testLogoCollectionRejectsPdf(): void
    {
        Storage::fake('public');
        $company = Company::factory()->createOne();

        $this->expectException(FileUnacceptableForCollection::class);

        $company->addMedia(UploadedFile::fake()->create('report.pdf', 10, 'application/pdf'))
            ->toMediaCollection(Company::LOGO_COLLECTION);
    }

    /**
     * @return void
     */
    public function testRasterLogoGetsWebpThumbnailWithinSizeLimit(): void
    {
        Storage::fake('public');
        $company = Company::factory()->createOne();

        $company->addMedia(UploadedFile::fake()->image('logo.png', 800, 400))
            ->usingFileName('logo.png')
            ->toMediaCollection(Company::LOGO_COLLECTION);

        $thumbPath = "companies/{$company->id}/conversions/logo-thumb.webp";
        Storage::disk('public')->assertExists($thumbPath);

        $size = getimagesize(Storage::disk('public')->path($thumbPath));

        $this->assertIsArray($size);
        $this->assertSame([128, 64, 'image/webp'], [$size[0], $size[1], $size['mime']]);
        $this->assertSame($thumbPath, $company->refresh()->logoThumbPath());
    }

    /**
     * @return void
     */
    public function testSvgLogoHasNoThumbnailAndUsesOriginal(): void
    {
        Storage::fake('public');
        $company = Company::factory()->createOne();

        $company->addMedia(UploadedFile::fake()->createWithContent(
            'logo.svg',
            '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><rect width="10" height="10"/></svg>',
        ))
            ->usingFileName('logo.svg')
            ->toMediaCollection(Company::LOGO_COLLECTION);

        $this->assertSame(["companies/{$company->id}/logo.svg"], Storage::disk('public')->allFiles());
        $this->assertSame("companies/{$company->id}/logo.svg", $company->refresh()->logoThumbPath());
    }

    /**
     * @return void
     */
    public function testCompanyWithoutLogoHasNoThumbPath(): void
    {
        $this->assertNull(Company::factory()->createOne()->logoThumbPath());
    }
}
