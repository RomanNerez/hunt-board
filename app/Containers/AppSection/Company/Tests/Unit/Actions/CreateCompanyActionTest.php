<?php

namespace App\Containers\AppSection\Company\Tests\Unit\Actions;

use App\Containers\AppSection\Company\Actions\CreateCompanyAction;
use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\Tests\UnitTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateCompanyAction::class)]
final class CreateCompanyActionTest extends UnitTestCase
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
    public function testCreatesCompanyWithOnlyName(): void
    {
        $company = app(CreateCompanyAction::class)->run(['name' => 'Grammarly']);

        $this->assertSame('Grammarly', $company->name);
        $this->assertNull($company->description);
        $this->assertNull($company->companyType);
        $this->assertCount(0, $company->domains);
        $this->assertNull($company->getFirstMedia(Company::LOGO_COLLECTION));
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    /**
     * @return void
     */
    public function testCreatesCompanyWithAllAttributes(): void
    {
        $product = CompanyType::factory()->createOne(['name' => 'Продуктова']);
        $utilities = Domain::factory()->createOne(['name' => 'Utilities']);
        $cybersecurity = Domain::factory()->createOne(['name' => 'Cybersecurity']);

        $company = app(CreateCompanyAction::class)->run([
            'name' => 'MacPaw',
            'description' => 'Утиліти для macOS',
            'company_type_id' => $product->id,
            'domains' => [(string) $utilities->id, $cybersecurity->id],
            'website_url' => 'https://macpaw.com',
            'vacancies_url' => 'https://macpaw.com/careers',
            'logo' => UploadedFile::fake()->image('macpaw-logo.png'),
        ]);

        $this->assertDatabaseHas(Company::class, [
            'id' => $company->id,
            'name' => 'MacPaw',
            'description' => 'Утиліти для macOS',
            'company_type_id' => $product->id,
            'website_url' => 'https://macpaw.com',
            'vacancies_url' => 'https://macpaw.com/careers',
        ]);
        $this->assertSame('Продуктова', $company->companyType?->name);
        $this->assertEqualsCanonicalizing(['Utilities', 'Cybersecurity'], $company->domains->pluck('name')->all());
        $this->assertTrue($company->relationLoaded('media'));
        Storage::disk('public')->assertExists("companies/{$company->id}/logo.png");
    }
}
