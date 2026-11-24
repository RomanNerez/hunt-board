<?php

namespace App\Containers\AppSection\Vacancy\Tests\Functional\Filament\Resources\Vacancies\Pages;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Contact\Models\Contact;
use App\Containers\AppSection\Status\Enums\StatusColor;
use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\Vacancy\Enums\Currency;
use App\Containers\AppSection\Vacancy\Enums\WorkFormat;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tests\FunctionalTestCase;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Pages\ListVacancies;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Schemas\VacancyForm;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Tables\VacanciesTable;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\VacancyResource;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Repeater;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ListVacancies::class)]
#[CoversClass(VacanciesTable::class)]
#[CoversClass(VacancyForm::class)]
final class ListVacanciesTest extends FunctionalTestCase
{
    /**
     * @var \Closure
     */
    private \Closure $undoRepeaterFake;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->undoRepeaterFake = Repeater::fake();
        Storage::fake('public');
        $this->actingAs(User::factory()->createOne());
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        ($this->undoRepeaterFake)();

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testTableShowsVacanciesWithCompaniesAndStatusBadges(): void
    {
        $macPaw = Company::factory()->createOne(['name' => 'MacPaw']);
        $grammarly = Company::factory()->createOne(['name' => 'Grammarly']);
        $applied = Status::factory()->createOne(['name' => 'Applied', 'color' => StatusColor::BLUE]);
        $offer = Status::factory()->createOne(['name' => 'Offer', 'color' => StatusColor::GREEN]);
        $php = Vacancy::factory()->createOne(['title' => 'PHP Developer', 'company_id' => $macPaw->id, 'status_id' => $applied->id]);
        $qa = Vacancy::factory()->createOne(['title' => 'QA Engineer', 'company_id' => $grammarly->id, 'status_id' => $offer->id]);

        Livewire::test(ListVacancies::class)
            ->assertCanSeeTableRecords([$php, $qa])
            ->assertSee(['MacPaw', 'Grammarly'])
            ->assertSeeHtml(StatusColor::BLUE->renderBadge('Applied')->toHtml())
            ->assertSeeHtml(StatusColor::GREEN->renderBadge('Offer')->toHtml());
    }

    /**
     * @return void
     */
    public function testTableShowsAllVacancySources(): void
    {
        $vacancy = Vacancy::factory()
            ->hasAttached(VacancySource::factory()->state(['name' => 'DOU']))
            ->hasAttached(VacancySource::factory()->state(['name' => 'LinkedIn']))
            ->createOne(['title' => 'PHP Developer']);

        Livewire::test(ListVacancies::class)
            ->assertCanSeeTableRecords([$vacancy])
            ->assertTableColumnStateSet('vacancySources.name', ['DOU', 'LinkedIn'], $vacancy)
            ->assertSee(['DOU', 'LinkedIn']);
    }

    /**
     * @return void
     */
    public function testTableHasNoWorkFormatSalaryAndNextActionColumns(): void
    {
        Vacancy::factory()->createOne([
            'work_format' => WorkFormat::HYBRID,
            'salary_min' => 4000,
            'salary_max' => 5500,
            'currency' => Currency::USD,
            'next_action_at' => '2026-11-25 10:00:00',
        ]);

        Livewire::test(ListVacancies::class)
            ->assertTableColumnDoesNotExist('work_format')
            ->assertTableColumnDoesNotExist('salary')
            ->assertTableColumnDoesNotExist('next_action_at')
            ->assertDontSee(['4 000–5 500 USD', '25.11.2026 10:00']);
    }

    /**
     * @return void
     */
    public function testTableIsSortedFromNewest(): void
    {
        $oldest = Vacancy::factory()->createOne(['created_at' => now()->subDays(2)]);
        $newest = Vacancy::factory()->createOne(['created_at' => now()]);
        $middle = Vacancy::factory()->createOne(['created_at' => now()->subDay()]);

        Livewire::test(ListVacancies::class)
            ->assertCanSeeTableRecords([$newest, $middle, $oldest], inOrder: true);
    }

    /**
     * @return void
     */
    public function testSearchByVacancyTitle(): void
    {
        $php = Vacancy::factory()->createOne(['title' => 'PHP Developer']);
        $qa = Vacancy::factory()->createOne(['title' => 'QA Engineer']);

        Livewire::test(ListVacancies::class)
            ->searchTable('PHP')
            ->assertCanSeeTableRecords([$php])
            ->assertCanNotSeeTableRecords([$qa]);
    }

    /**
     * @return void
     */
    public function testSearchByCompanyName(): void
    {
        $macPawVacancy = Vacancy::factory()->for(Company::factory()->state(['name' => 'MacPaw']))->createOne();
        $grammarlyVacancy = Vacancy::factory()->for(Company::factory()->state(['name' => 'Grammarly']))->createOne();

        Livewire::test(ListVacancies::class)
            ->searchTable('Gramm')
            ->assertCanSeeTableRecords([$grammarlyVacancy])
            ->assertCanNotSeeTableRecords([$macPawVacancy]);
    }

    /**
     * @return void
     */
    public function testFiltersByStatus(): void
    {
        $offer = Status::factory()->createOne(['name' => 'Offer']);
        $appliedVacancy = Vacancy::factory()->for(Status::factory()->state(['name' => 'Applied']))->createOne();
        $offerVacancy = Vacancy::factory()->createOne(['status_id' => $offer->id]);

        Livewire::test(ListVacancies::class)
            ->filterTable('status', $offer->id)
            ->assertCanSeeTableRecords([$offerVacancy])
            ->assertCanNotSeeTableRecords([$appliedVacancy]);
    }

    /**
     * @return void
     */
    public function testFiltersByOneOfSeveralVacancySources(): void
    {
        $linkedIn = VacancySource::factory()->createOne(['name' => 'LinkedIn']);
        $php = Vacancy::factory()
            ->hasAttached(VacancySource::factory()->state(['name' => 'DOU']))
            ->hasAttached($linkedIn)
            ->createOne(['title' => 'PHP Developer']);
        $qa = Vacancy::factory()
            ->hasAttached(VacancySource::factory()->state(['name' => 'Djinni']))
            ->createOne(['title' => 'QA Engineer']);

        Livewire::test(ListVacancies::class)
            ->filterTable('vacancySources', $linkedIn->id)
            ->assertCanSeeTableRecords([$php])
            ->assertCanNotSeeTableRecords([$qa]);
    }

    /**
     * @return void
     */
    public function testFiltersByWorkFormat(): void
    {
        $remote = Vacancy::factory()->createOne(['work_format' => WorkFormat::REMOTE]);
        $office = Vacancy::factory()->createOne(['work_format' => WorkFormat::OFFICE]);

        Livewire::test(ListVacancies::class)
            ->filterTable('work_format', WorkFormat::REMOTE->value)
            ->assertCanSeeTableRecords([$remote])
            ->assertCanNotSeeTableRecords([$office]);
    }

    /**
     * @return void
     */
    public function testRowHasOnlyViewActionAndTableHasNoBulkActions(): void
    {
        $vacancy = Vacancy::factory()->createOne();

        Livewire::test(ListVacancies::class)
            ->assertActionExists(TestAction::make('view')->table($vacancy))
            ->assertActionDoesNotExist(TestAction::make('edit')->table($vacancy))
            ->assertActionDoesNotExist(TestAction::make('delete')->table($vacancy))
            ->assertActionDoesNotExist(TestAction::make('delete')->table()->bulk())
            ->assertTableActionHasUrl('view', VacancyResource::getUrl('view', ['record' => $vacancy]), $vacancy);
    }

    /**
     * @return void
     */
    public function testCreatesVacancyInModalAndRedirectsToOverview(): void
    {
        $macPaw = Company::factory()->createOne(['name' => 'MacPaw']);
        $applied = Status::factory()->createOne(['name' => 'Applied']);

        $component = Livewire::test(ListVacancies::class)
            ->callAction(TestAction::make('create'), data: [
                ...$this->validData(),
                'company_id' => $macPaw->id,
                'status_id' => $applied->id,
                'work_format' => WorkFormat::REMOTE->value,
                'salary_min' => 4000,
                'salary_max' => 5500,
                'currency' => Currency::USD->value,
            ])
            ->assertHasNoFormErrors();

        $vacancy = Vacancy::query()->where('title', 'Senior PHP Developer')->firstOrFail();

        $component->assertRedirect(VacancyResource::getUrl('view', ['record' => $vacancy]));
        $this->assertSame(WorkFormat::REMOTE, $vacancy->work_format);
        $this->assertSame(5500, $vacancy->salary_max);
        $this->assertDatabaseMissing('vacancy_vacancy_source', ['vacancy_id' => $vacancy->id]);
    }

    /**
     * @return void
     */
    public function testCreatesVacancyWithTwoSourcesAndTheirLinks(): void
    {
        $dou = VacancySource::factory()->createOne(['name' => 'DOU']);
        $linkedIn = VacancySource::factory()->createOne(['name' => 'LinkedIn']);

        Livewire::test(ListVacancies::class)
            ->callAction(TestAction::make('create'), data: [
                ...$this->validData(),
                'sources' => [
                    ['vacancy_source_id' => $dou->id, 'url' => 'https://jobs.dou.ua/vacancies/123/'],
                    ['vacancy_source_id' => $linkedIn->id, 'url' => 'https://www.linkedin.com/jobs/view/456/'],
                ],
            ])
            ->assertHasNoFormErrors();

        $vacancy = Vacancy::query()->where('title', 'Senior PHP Developer')->firstOrFail();

        $this->assertDatabaseCount('vacancy_vacancy_source', 2);
        $this->assertDatabaseHas('vacancy_vacancy_source', ['vacancy_id' => $vacancy->id, 'vacancy_source_id' => $dou->id, 'url' => 'https://jobs.dou.ua/vacancies/123/']);
        $this->assertDatabaseHas('vacancy_vacancy_source', ['vacancy_id' => $vacancy->id, 'vacancy_source_id' => $linkedIn->id, 'url' => 'https://www.linkedin.com/jobs/view/456/']);
    }

    /**
     * @return void
     */
    public function testCreatesVacancySourceWithoutLink(): void
    {
        $dou = VacancySource::factory()->createOne(['name' => 'DOU']);

        Livewire::test(ListVacancies::class)
            ->callAction(TestAction::make('create'), data: [
                ...$this->validData(),
                'sources' => [['vacancy_source_id' => $dou->id, 'url' => '']],
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('vacancy_vacancy_source', ['vacancy_source_id' => $dou->id, 'url' => null]);
    }

    /**
     * @return void
     */
    public function testCreateRejectsDuplicateVacancySource(): void
    {
        $dou = VacancySource::factory()->createOne(['name' => 'DOU']);

        Livewire::test(ListVacancies::class)
            ->callAction(TestAction::make('create'), data: [
                ...$this->validData(),
                'sources' => [
                    ['vacancy_source_id' => $dou->id, 'url' => 'https://jobs.dou.ua/vacancies/123/'],
                    ['vacancy_source_id' => $dou->id, 'url' => null],
                ],
            ])
            ->assertHasFormErrors(['sources.1.vacancy_source_id']);

        $this->assertDatabaseCount(Vacancy::class, 0);
        $this->assertDatabaseCount('vacancy_vacancy_source', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidDataProvider(): array
    {
        return [
            'empty title' => [['title' => ''], 'title'],
            'empty company' => [['company_id' => null], 'company_id'],
            'empty status' => [['status_id' => null], 'status_id'],
            'link without source' => [['sources' => [['vacancy_source_id' => null, 'url' => 'https://jobs.dou.ua/vacancies/123/']]], 'sources.0.vacancy_source_id'],
            'unknown work format' => [['work_format' => 'freelance'], 'work_format'],
            'negative salary' => [['salary_min' => -100, 'currency' => 'USD'], 'salary_min'],
            'salary without currency' => [['salary_min' => 3000], 'currency'],
            'maximum below minimum' => [['salary_min' => 5000, 'salary_max' => 4000, 'currency' => 'USD'], 'salary_max'],
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
        Livewire::test(ListVacancies::class)
            ->callAction(TestAction::make('create'), data: [...$this->validData(), ...$data])
            ->assertHasFormErrors([$field]);

        $this->assertDatabaseCount(Vacancy::class, 0);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidSourceLinkProvider(): array
    {
        return [
            'without scheme' => ['dou.ua/vacancy/123'],
            'with ftp scheme' => ['ftp://example.com/vacancy'],
        ];
    }

    /**
     * @param string $url
     *
     * @return void
     */
    #[DataProvider('invalidSourceLinkProvider')]
    public function testCreateRejectsInvalidSourceLink(string $url): void
    {
        $dou = VacancySource::factory()->createOne(['name' => 'DOU']);

        Livewire::test(ListVacancies::class)
            ->callAction(TestAction::make('create'), data: [
                ...$this->validData(),
                'sources' => [['vacancy_source_id' => $dou->id, 'url' => $url]],
            ])
            ->assertHasFormErrors(['sources.0.url']);

        $this->assertDatabaseCount(Vacancy::class, 0);
    }

    /**
     * @return void
     */
    public function testCreateAcceptsOnlyMaximumSalary(): void
    {
        Livewire::test(ListVacancies::class)
            ->callAction(TestAction::make('create'), data: [...$this->validData(), 'salary_max' => 5000, 'currency' => 'EUR'])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Vacancy::class, ['salary_min' => null, 'salary_max' => 5000, 'currency' => 'EUR']);
    }

    /**
     * @return void
     */
    public function testCreatesCompanyFromVacancyFormAndSelectsIt(): void
    {
        $component = Livewire::test(ListVacancies::class)
            ->mountAction(TestAction::make('create'))
            ->callAction(TestAction::make('createOption')->schemaComponent('company_id'), data: ['name' => 'Grammarly'])
            ->assertHasNoFormErrors();

        $grammarly = Company::query()->where('name', 'Grammarly')->firstOrFail();

        $component->assertSchemaStateSet(['company_id' => $grammarly->id]);
    }

    /**
     * @return void
     */
    public function testCreatingDuplicateCompanyFromVacancyFormIsRejected(): void
    {
        Company::factory()->createOne(['name' => 'MacPaw']);

        Livewire::test(ListVacancies::class)
            ->mountAction(TestAction::make('create'))
            ->callAction(TestAction::make('createOption')->schemaComponent('company_id'), data: ['name' => 'MacPaw'])
            ->assertHasFormErrors(['name' => 'unique']);

        $this->assertDatabaseCount(Company::class, 1);
    }

    /**
     * @return void
     */
    public function testCreatesCompanyWithLogoFromVacancyForm(): void
    {
        Livewire::test(ListVacancies::class)
            ->mountAction(TestAction::make('create'))
            ->callAction(TestAction::make('createOption')->schemaComponent('company_id'), data: [
                'name' => 'Grammarly',
                'logo' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertHasNoFormErrors();

        $grammarly = Company::query()->where('name', 'Grammarly')->firstOrFail();

        Storage::disk('public')->assertExists("companies/{$grammarly->id}/logo.png");
    }

    /**
     * @return void
     */
    public function testCreatesVacancySourceFromVacancyFormAndSelectsIt(): void
    {
        $component = Livewire::test(ListVacancies::class)
            ->mountAction(TestAction::make('create'))
            ->fillForm(['sources' => [['vacancy_source_id' => null, 'url' => null]]])
            ->callAction(TestAction::make('createOption')->schemaComponent('sources.0.vacancy_source_id'), data: ['name' => 'Telegram'])
            ->assertHasNoFormErrors();

        $telegram = VacancySource::query()->where('name', 'Telegram')->firstOrFail();

        $component->assertSchemaStateSet(['sources' => [['vacancy_source_id' => $telegram->id, 'url' => null]]]);
    }

    /**
     * @return void
     */
    public function testCreatesVacancyWithTwoContacts(): void
    {
        $olena = Contact::factory()->createOne(['name' => 'Олена Коваль']);
        $andrii = Contact::factory()->createOne(['name' => 'Андрій Мельник']);

        Livewire::test(ListVacancies::class)
            ->callAction(TestAction::make('create'), data: [
                ...$this->validData(),
                'contacts' => [$olena->id, $andrii->id],
            ])
            ->assertHasNoFormErrors();

        $vacancy = Vacancy::query()->where('title', 'Senior PHP Developer')->firstOrFail();

        $this->assertDatabaseCount('contact_vacancy', 2);
        $this->assertDatabaseHas('contact_vacancy', ['vacancy_id' => $vacancy->id, 'contact_id' => $olena->id]);
        $this->assertDatabaseHas('contact_vacancy', ['vacancy_id' => $vacancy->id, 'contact_id' => $andrii->id]);
    }

    /**
     * @return void
     */
    public function testCreatesContactWithSourceFromVacancyFormAndSelectsIt(): void
    {
        $linkedIn = VacancySource::factory()->createOne(['name' => 'LinkedIn']);

        $component = Livewire::test(ListVacancies::class)
            ->mountAction(TestAction::make('create'))
            ->callAction(TestAction::make('createOption')->schemaComponent('contacts'), data: [
                'name' => 'Олена Коваль',
                'sources' => [['vacancy_source_id' => $linkedIn->id, 'url' => 'https://www.linkedin.com/in/olena-koval/']],
            ])
            ->assertHasNoFormErrors();

        $olena = Contact::query()->where('name', 'Олена Коваль')->firstOrFail();

        $component->assertSchemaStateSet(['contacts' => [$olena->id]]);
        $this->assertDatabaseHas('contact_vacancy_source', ['contact_id' => $olena->id, 'vacancy_source_id' => $linkedIn->id, 'url' => 'https://www.linkedin.com/in/olena-koval/']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validData(): array
    {
        return [
            'title' => 'Senior PHP Developer',
            'company_id' => Company::factory()->createOne()->id,
            'status_id' => Status::factory()->createOne()->id,
        ];
    }
}
