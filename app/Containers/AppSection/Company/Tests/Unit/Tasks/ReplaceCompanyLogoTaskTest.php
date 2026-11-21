<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Tasks;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Tasks\ReplaceCompanyLogoTask;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;

use function Safe\file_get_contents;
use function Safe\parse_url;

#[CoversClass(ReplaceCompanyLogoTask::class)]
final class ReplaceCompanyLogoTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    /**
     * @return void
     */
    public function testStoresPngUnderCompanyIdIgnoringOriginalName(): void
    {
        $company = Company::factory()->createOne();

        app(ReplaceCompanyLogoTask::class)->run($company, UploadedFile::fake()->image('MacPaw Logo (final).png'));

        Storage::disk('public')->assertExists("companies/{$company->id}/logo.png");
        $this->assertSame(["companies/{$company->id}/conversions/logo-thumb.webp", "companies/{$company->id}/logo.png"], Storage::disk('public')->allFiles());
        $this->assertSame("/storage/companies/{$company->id}/logo.png", parse_url($company->refresh()->getFirstMediaUrl(Company::LOGO_COLLECTION), PHP_URL_PATH));
    }

    /**
     * @return void
     */
    public function testUsesExtensionOfUploadedFormat(): void
    {
        $company = Company::factory()->createOne();

        app(ReplaceCompanyLogoTask::class)->run($company, UploadedFile::fake()->image('brand.webp'));

        Storage::disk('public')->assertExists("companies/{$company->id}/logo.webp");
    }

    /**
     * @return void
     */
    public function testNormalizesJpegExtension(): void
    {
        $company = Company::factory()->createOne();

        app(ReplaceCompanyLogoTask::class)->run($company, UploadedFile::fake()->image('photo.JPEG'));

        Storage::disk('public')->assertExists("companies/{$company->id}/logo.jpg");
    }

    /**
     * @return void
     */
    public function testReplacingWithAnotherFormatLeavesOnlyNewFile(): void
    {
        $company = Company::factory()->createOne();
        $task = app(ReplaceCompanyLogoTask::class);
        $task->run($company, UploadedFile::fake()->image('logo.png'));

        $task->run($company, $this->svgLogo());

        $this->assertSame(["companies/{$company->id}/logo.svg"], Storage::disk('public')->allFiles());
        $this->assertSame(1, $company->media()->count());
    }

    /**
     * @return void
     */
    public function testReplacingWithSameFormatKeepsFileWithNewContent(): void
    {
        $company = Company::factory()->createOne();
        $task = app(ReplaceCompanyLogoTask::class);
        $task->run($company, UploadedFile::fake()->image('old.png', 10, 10));
        $newLogo = UploadedFile::fake()->image('new.png', 64, 64);
        $newContent = file_get_contents($newLogo->getRealPath());

        $task->run($company, $newLogo);

        $this->assertSame(["companies/{$company->id}/conversions/logo-thumb.webp", "companies/{$company->id}/logo.png"], Storage::disk('public')->allFiles());
        $this->assertSame($newContent, Storage::disk('public')->get("companies/{$company->id}/logo.png"));
    }

    /**
     * @return UploadedFile
     */
    private function svgLogo(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            'logo.svg',
            '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"><rect width="10" height="10"/></svg>',
        );
    }
}
