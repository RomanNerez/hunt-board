<?php

namespace App\Containers\AppSection\Company\Tests\Functional\Filament\Resources\Companies\Pages;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\Tasks\ReplaceCompanyLogoTask;
use App\Containers\AppSection\Company\Tests\FunctionalTestCase;
use App\Containers\AppSection\Company\UI\Filament\Resources\Companies\Pages\ManageCompanies;
use App\Containers\AppSection\Company\UI\Filament\Resources\Companies\Tables\CompaniesTable;
use App\Containers\AppSection\User\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ManageCompanies::class)]
#[CoversClass(CompaniesTable::class)]
final class ManageCompaniesTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->createOne());
    }

    /**
     * @return void
     */
    public function testCreateOpensModalOnSamePage(): void
    {
        Livewire::test(ManageCompanies::class)
            ->mountAction(TestAction::make('create'))
            ->assertActionMounted(TestAction::make('create'))
            ->assertFormFieldExists('name')
            ->assertFormFieldExists('logo');
    }

    /**
     * @return void
     */
    public function testCreatesCompanyWithOnlyName(): void
    {
        Livewire::test(ManageCompanies::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'Grammarly'])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $company = Company::query()->where('name', 'Grammarly')->firstOrFail();

        $this->assertNull($company->company_type_id);
        $this->assertNull($company->website_url);
        $this->assertSame(0, $company->domains()->count());
        $this->assertSame(0, $company->media()->count());
    }

    /**
     * @return void
     */
    public function testCreatesCompanyWithAllAttributesAndLogo(): void
    {
        $product = CompanyType::factory()->createOne(['name' => 'Продуктова']);
        $utilities = Domain::factory()->createOne(['name' => 'Utilities']);
        $cybersecurity = Domain::factory()->createOne(['name' => 'Cybersecurity']);

        Livewire::test(ManageCompanies::class)
            ->callAction(TestAction::make('create'), data: [
                'name' => 'MacPaw',
                'description' => 'Утиліти для macOS',
                'company_type_id' => $product->id,
                'domains' => [$utilities->id, $cybersecurity->id],
                'website_url' => 'https://macpaw.com',
                'vacancies_url' => 'https://macpaw.com/careers',
                'logo' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertHasNoFormErrors();

        $company = Company::query()->where('name', 'MacPaw')->firstOrFail();

        $this->assertSame($product->id, $company->company_type_id);
        $this->assertSame('https://macpaw.com/careers', $company->vacancies_url);
        $this->assertEqualsCanonicalizing(['Utilities', 'Cybersecurity'], $company->domains()->pluck('name')->all());
        Storage::disk('public')->assertExists("companies/{$company->id}/logo.png");
    }

    /**
     * @return void
     */
    public function testCreatesCompanyWithVacanciesUrlOnly(): void
    {
        Livewire::test(ManageCompanies::class)
            ->callAction(TestAction::make('create'), data: [
                'name' => 'MacPaw',
                'vacancies_url' => 'https://jobs.dou.ua/companies/macpaw/vacancies/',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Company::class, [
            'name' => 'MacPaw',
            'website_url' => null,
            'vacancies_url' => 'https://jobs.dou.ua/companies/macpaw/vacancies/',
        ]);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidDataProvider(): array
    {
        return [
            'empty name' => [['name' => ''], 'name'],
            'description too long' => [['description' => str_repeat('a', 1001)], 'description'],
            'website without scheme' => [['website_url' => 'macpaw.com'], 'website_url'],
            'website with ftp scheme' => [['website_url' => 'ftp://macpaw.com'], 'website_url'],
            'vacancies not a url' => [['vacancies_url' => 'not a url'], 'vacancies_url'],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param string $field
     *
     * @return void
     */
    #[DataProvider('invalidDataProvider')]
    public function testCreateRejectsInvalidField(array $data, string $field): void
    {
        Livewire::test(ManageCompanies::class)
            ->callAction(TestAction::make('create'), data: [...['name' => 'MacPaw'], ...$data])
            ->assertHasFormErrors([$field]);

        $this->assertDatabaseCount(Company::class, 0);
    }

    /**
     * @return void
     */
    public function testCreateRejectsDuplicateName(): void
    {
        Company::factory()->createOne(['name' => 'MacPaw']);

        Livewire::test(ManageCompanies::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'MacPaw'])
            ->assertHasFormErrors(['name' => 'unique']);

        $this->assertDatabaseCount(Company::class, 1);
    }

    /**
     * @return array<string, array{\Closure(): UploadedFile}>
     */
    public static function invalidLogoProvider(): array
    {
        return [
            'pdf' => [static fn (): UploadedFile => UploadedFile::fake()->create('report.pdf', 10, 'application/pdf')],
            '3 MB image' => [static fn (): UploadedFile => UploadedFile::fake()->image('big.png')->size(3072)],
        ];
    }

    /**
     * @param \Closure(): UploadedFile $makeFile
     *
     * @return void
     */
    #[DataProvider('invalidLogoProvider')]
    public function testCreateRejectsInvalidLogo(\Closure $makeFile): void
    {
        Livewire::test(ManageCompanies::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'MacPaw', 'logo' => $makeFile()])
            ->assertHasFormErrors(['logo']);

        $this->assertDatabaseCount(Company::class, 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    /**
     * @return void
     */
    public function testCreatesCompanyTypeFromCompanyForm(): void
    {
        $component = Livewire::test(ManageCompanies::class)
            ->mountAction(TestAction::make('create'))
            ->callAction(TestAction::make('createOption')->schemaComponent('company_type_id'), data: ['name' => 'Стартап']);

        $startup = CompanyType::query()->where('name', 'Стартап')->firstOrFail();

        $component->assertSchemaStateSet(['company_type_id' => $startup->id]);
    }

    /**
     * @return void
     */
    public function testCreatesDomainFromCompanyForm(): void
    {
        $utilities = Domain::factory()->createOne(['name' => 'Utilities']);

        $component = Livewire::test(ManageCompanies::class)
            ->mountAction(TestAction::make('create'))
            ->fillForm(['domains' => [$utilities->id]])
            ->callAction(TestAction::make('createOption')->schemaComponent('domains'), data: ['name' => 'HealthTech']);

        $healthTech = Domain::query()->where('name', 'HealthTech')->firstOrFail();

        $component->assertSchemaStateSet(['domains' => [$utilities->id, $healthTech->id]]);
    }

    /**
     * @return void
     */
    public function testEditFormIsFilledWithDomainsAndLogo(): void
    {
        $company = Company::factory()->withDomains(2)->createOne();
        app(ReplaceCompanyLogoTask::class)->run($company, UploadedFile::fake()->image('logo.png'));

        Livewire::test(ManageCompanies::class)
            ->mountAction(TestAction::make('edit')->table($company))
            ->assertSchemaStateSet([
                'name' => $company->name,
                'domains' => $company->domains()->orderBy('domains.id')->pluck('domains.id')->all(),
            ]);

        $this->assertNotNull($company->getFirstMedia(Company::LOGO_COLLECTION));
    }

    /**
     * @return void
     */
    public function testEditingDescriptionKeepsLogoAndName(): void
    {
        $company = Company::factory()->createOne(['name' => 'MacPaw']);
        app(ReplaceCompanyLogoTask::class)->run($company, UploadedFile::fake()->image('logo.png'));
        $mediaId = $company->media()->value('id');

        Livewire::test(ManageCompanies::class)
            ->callAction(TestAction::make('edit')->table($company), data: ['description' => 'Утиліти для macOS'])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Company::class, ['id' => $company->id, 'name' => 'MacPaw', 'description' => 'Утиліти для macOS']);
        $this->assertSame($mediaId, $company->media()->value('id'));
        Storage::disk('public')->assertExists("companies/{$company->id}/logo.png");
    }

    /**
     * @return void
     */
    public function testEditReplacesLogo(): void
    {
        $company = Company::factory()->createOne();
        app(ReplaceCompanyLogoTask::class)->run($company, UploadedFile::fake()->image('logo.jpg'));

        Livewire::test(ManageCompanies::class)
            ->mountAction(TestAction::make('edit')->table($company))
            ->fillForm(['logo' => [UploadedFile::fake()->image('logo.png')]])
            ->callMountedAction()
            ->assertHasNoFormErrors();

        $this->assertSame(["companies/{$company->id}/conversions/logo-thumb.webp", "companies/{$company->id}/logo.png"], Storage::disk('public')->allFiles());
    }

    /**
     * @return void
     */
    public function testEditRemovesLogo(): void
    {
        $company = Company::factory()->createOne();
        app(ReplaceCompanyLogoTask::class)->run($company, UploadedFile::fake()->image('logo.png'));

        Livewire::test(ManageCompanies::class)
            ->callAction(TestAction::make('edit')->table($company), data: ['logo' => null])
            ->assertHasNoFormErrors();

        $this->assertSame(0, $company->media()->count());
        Storage::disk('public')->assertMissing("companies/{$company->id}");
    }

    /**
     * @return void
     */
    public function testEditRemovesOneOfTwoDomains(): void
    {
        $utilities = Domain::factory()->createOne(['name' => 'Utilities']);
        $cybersecurity = Domain::factory()->createOne(['name' => 'Cybersecurity']);
        $company = Company::factory()->createOne(['name' => 'MacPaw']);
        $company->domains()->attach([$utilities->id, $cybersecurity->id]);

        Livewire::test(ManageCompanies::class)
            ->callAction(TestAction::make('edit')->table($company), data: ['domains' => [$utilities->id]])
            ->assertHasNoFormErrors();

        $this->assertSame(['Utilities'], $company->domains()->pluck('name')->all());
        $this->assertModelExists($cybersecurity);
    }

    /**
     * @return void
     */
    public function testListsCompaniesByNameWithLogoOrPlaceholder(): void
    {
        $macpaw = Company::factory()->createOne(['name' => 'MacPaw']);
        app(ReplaceCompanyLogoTask::class)->run($macpaw, UploadedFile::fake()->image('logo.png'));
        $grammarly = Company::factory()->createOne(['name' => 'Grammarly']);

        Livewire::test(ManageCompanies::class)
            ->assertCanSeeTableRecords([$grammarly, $macpaw], inOrder: true)
            ->assertSeeHtml("/storage/companies/{$macpaw->id}/conversions/logo-thumb.webp")
            ->assertSeeHtml(CompaniesTable::LOGO_PLACEHOLDER);
    }

    /**
     * @return void
     */
    public function testListsVacanciesLinkOpeningInNewTab(): void
    {
        $vacanciesUrl = 'https://jobs.dou.ua/companies/macpaw/vacancies/';
        $macpaw = Company::factory()->createOne(['name' => 'MacPaw', 'vacancies_url' => $vacanciesUrl]);

        Livewire::test(ManageCompanies::class)
            ->assertTableColumnExists('vacancies_url')
            ->assertTableColumnStateSet('vacancies_url', $vacanciesUrl, $macpaw)
            ->assertSeeHtml('href="' . $vacanciesUrl . '"')
            ->assertSeeHtml('target="_blank"');
    }

    /**
     * @return void
     */
    public function testListsSeveralCompaniesWithLogosWithoutLazyLoading(): void
    {
        $companies = Company::factory()->withType()->withDomains(2)->count(3)->create();

        foreach ($companies as $company) {
            app(ReplaceCompanyLogoTask::class)->run($company, UploadedFile::fake()->image('logo.png'));
        }

        config(['media-library.force_lazy_loading' => false]);

        Livewire::test(ManageCompanies::class)
            ->assertCanSeeTableRecords($companies)
            ->assertOk();
    }

    /**
     * @return void
     */
    public function testSearchFiltersByName(): void
    {
        $macpaw = Company::factory()->createOne(['name' => 'MacPaw']);
        $grammarly = Company::factory()->createOne(['name' => 'Grammarly']);

        Livewire::test(ManageCompanies::class)
            ->searchTable('Mac')
            ->assertCanSeeTableRecords([$macpaw])
            ->assertCanNotSeeTableRecords([$grammarly]);
    }

    /**
     * @return void
     */
    public function testFiltersByDomain(): void
    {
        $utilities = Domain::factory()->createOne(['name' => 'Utilities']);
        $edtech = Domain::factory()->createOne(['name' => 'EdTech']);
        $macpaw = Company::factory()->createOne(['name' => 'MacPaw']);
        $macpaw->domains()->attach($utilities->id);
        $grammarly = Company::factory()->createOne(['name' => 'Grammarly']);
        $grammarly->domains()->attach($edtech->id);

        Livewire::test(ManageCompanies::class)
            ->filterTable('domains', $edtech->id)
            ->assertCanSeeTableRecords([$grammarly])
            ->assertCanNotSeeTableRecords([$macpaw]);
    }

    /**
     * @return void
     */
    public function testFiltersByType(): void
    {
        $product = CompanyType::factory()->createOne(['name' => 'Продуктова']);
        $outsource = CompanyType::factory()->createOne(['name' => 'Аутсорс']);
        $macpaw = Company::factory()->createOne(['name' => 'MacPaw', 'company_type_id' => $product->id]);
        $epam = Company::factory()->createOne(['name' => 'EPAM', 'company_type_id' => $outsource->id]);

        Livewire::test(ManageCompanies::class)
            ->filterTable('company_type_id', $outsource->id)
            ->assertCanSeeTableRecords([$epam])
            ->assertCanNotSeeTableRecords([$macpaw]);
    }

    /**
     * @return void
     */
    public function testDeletesSingleCompanyWithLogo(): void
    {
        $company = Company::factory()->createOne();
        app(ReplaceCompanyLogoTask::class)->run($company, UploadedFile::fake()->image('logo.png'));

        Livewire::test(ManageCompanies::class)
            ->callAction(TestAction::make('delete')->table($company))
            ->assertNotified();

        $this->assertModelMissing($company);
        Storage::disk('public')->assertMissing("companies/{$company->id}");
    }

    /**
     * @return void
     */
    public function testBulkDeletesCompaniesAndTheirLogos(): void
    {
        $macpaw = Company::factory()->createOne(['name' => 'MacPaw']);
        $grammarly = Company::factory()->createOne(['name' => 'Grammarly']);
        $kept = Company::factory()->createOne(['name' => 'EPAM']);

        foreach ([$macpaw, $grammarly] as $company) {
            app(ReplaceCompanyLogoTask::class)->run($company, UploadedFile::fake()->image('logo.png'));
        }

        Livewire::test(ManageCompanies::class)
            ->selectTableRecords([$macpaw, $grammarly])
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertNotified();

        $this->assertModelMissing($macpaw);
        $this->assertModelMissing($grammarly);
        $this->assertModelExists($kept);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
