<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Actions;

use App\Containers\AppSection\Company\Actions\UpdateCompanyAction;
use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\Tasks\ReplaceCompanyLogoTask;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateCompanyAction::class)]
final class UpdateCompanyActionTest extends UnitTestCase
{
    /**
     * @var Company
     */
    private Company $company;

    /**
     * @var string
     */
    private string $logoPath;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->company = Company::factory()->createOne(['name' => 'MacPaw']);
        app(ReplaceCompanyLogoTask::class)->run($this->company, UploadedFile::fake()->image('logo.png'));
        $this->logoPath = "companies/{$this->company->id}/logo.png";
    }

    /**
     * @return void
     */
    public function testUpdateWithoutLogoKeyKeepsLogo(): void
    {
        $mediaId = $this->company->getFirstMedia(Company::LOGO_COLLECTION)?->id;

        $result = app(UpdateCompanyAction::class)->run($this->company->id, ['description' => 'Утиліти для macOS']);

        $this->assertSame('Утиліти для macOS', $result->description);
        $this->assertSame($mediaId, $result->getFirstMedia(Company::LOGO_COLLECTION)?->id);
        Storage::disk('public')->assertExists($this->logoPath);
    }

    /**
     * @return void
     */
    public function testUpdateWithExistingLogoPathKeepsLogo(): void
    {
        $mediaId = $this->company->getFirstMedia(Company::LOGO_COLLECTION)?->id;

        $result = app(UpdateCompanyAction::class)->run($this->company->id, [
            'description' => 'Утиліти для macOS',
            'logo' => $this->logoPath,
        ]);

        $this->assertSame($mediaId, $result->getFirstMedia(Company::LOGO_COLLECTION)?->id);
        Storage::disk('public')->assertExists($this->logoPath);
    }

    /**
     * @return void
     */
    public function testNullLogoRemovesLogoAndDirectory(): void
    {
        $result = app(UpdateCompanyAction::class)->run($this->company->id, ['logo' => null]);

        $this->assertNull($result->getFirstMedia(Company::LOGO_COLLECTION));
        Storage::disk('public')->assertMissing("companies/{$this->company->id}");
    }

    /**
     * @return void
     */
    public function testUploadedLogoReplacesPrevious(): void
    {
        $result = app(UpdateCompanyAction::class)->run($this->company->id, ['logo' => UploadedFile::fake()->image('new.webp')]);

        $this->assertSame('logo.webp', $result->getFirstMedia(Company::LOGO_COLLECTION)?->file_name);
        $this->assertSame(["companies/{$this->company->id}/conversions/logo-thumb.webp", "companies/{$this->company->id}/logo.webp"], Storage::disk('public')->allFiles());
    }

    /**
     * @return void
     */
    public function testDomainsAreSyncedOnlyWhenGiven(): void
    {
        $utilities = Domain::factory()->createOne(['name' => 'Utilities']);
        $cybersecurity = Domain::factory()->createOne(['name' => 'Cybersecurity']);
        $this->company->domains()->attach([$utilities->id, $cybersecurity->id]);

        app(UpdateCompanyAction::class)->run($this->company->id, ['description' => 'Утиліти']);
        $this->assertSame(2, $this->company->domains()->count());

        $result = app(UpdateCompanyAction::class)->run($this->company->id, ['domains' => [$utilities->id]]);
        $this->assertSame(['Utilities'], $result->domains->pluck('name')->all());
        $this->assertModelExists($cybersecurity);
    }
}
