<?php

namespace App\Containers\AppSection\Contact\Tests\Functional\Filament\Resources\Contacts\Pages;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Contact\Models\Contact;
use App\Containers\AppSection\Contact\Tests\FunctionalTestCase;
use App\Containers\AppSection\Contact\UI\Filament\Resources\Contacts\Pages\ManageContacts;
use App\Containers\AppSection\Contact\UI\Filament\Resources\Contacts\Schemas\ContactForm;
use App\Containers\AppSection\Contact\UI\Filament\Resources\Contacts\Tables\ContactsTable;
use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Pages\ViewVacancy;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Repeater;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ManageContacts::class)]
#[CoversClass(ContactsTable::class)]
#[CoversClass(ContactForm::class)]
final class ManageContactsTest extends FunctionalTestCase
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
    public function testTableShowsNameAndAllSources(): void
    {
        $contact = Contact::factory()
            ->hasAttached(VacancySource::factory()->state(['name' => 'LinkedIn']), ['url' => 'https://www.linkedin.com/in/olena-koval/'])
            ->hasAttached(VacancySource::factory()->state(['name' => 'Telegram']), ['url' => null])
            ->createOne(['name' => 'Олена Коваль']);

        Livewire::test(ManageContacts::class)
            ->assertCanSeeTableRecords([$contact])
            ->assertTableColumnStateSet('name', 'Олена Коваль', $contact)
            ->assertTableColumnStateSet('vacancySources.name', ['LinkedIn', 'Telegram'], $contact)
            ->assertSee(['Олена Коваль', 'LinkedIn', 'Telegram']);
    }

    /**
     * @return void
     */
    public function testTableShowsLinkedVacancy(): void
    {
        $vacancy = Vacancy::factory()->createOne(['title' => 'PHP Developer']);
        $contact = Contact::factory()->hasAttached($vacancy)->createOne(['name' => 'Олена Коваль']);
        $unlinked = Contact::factory()->createOne(['name' => 'Андрій Мельник']);

        Livewire::test(ManageContacts::class)
            ->assertTableColumnStateSet('vacancies.title', ['PHP Developer'], $contact)
            ->assertTableColumnStateNotSet('vacancies.title', ['PHP Developer'], $unlinked)
            ->assertSee('PHP Developer');
    }

    /**
     * @return void
     */
    public function testOneContactIsLinkedToVacanciesOfDifferentCompanies(): void
    {
        $macPawVacancy = Vacancy::factory()->for(Company::factory()->state(['name' => 'MacPaw']))->createOne(['title' => 'PHP Developer']);
        $grammarlyVacancy = Vacancy::factory()->for(Company::factory()->state(['name' => 'Grammarly']))->createOne(['title' => 'QA Engineer']);
        $contact = Contact::factory()->hasAttached($macPawVacancy)->createOne(['name' => 'Олена Коваль']);

        Livewire::test(ViewVacancy::class, ['record' => $grammarlyVacancy->id])
            ->callAction(TestAction::make('edit'), data: ['contacts' => [$contact->id]])
            ->assertHasNoFormErrors();

        Livewire::test(ManageContacts::class)
            ->assertTableColumnStateSet('vacancies.title', ['PHP Developer', 'QA Engineer'], $contact);
    }

    /**
     * @return void
     */
    public function testDeletingContactKeepsVacancyWithoutIt(): void
    {
        $vacancy = Vacancy::factory()->createOne(['title' => 'PHP Developer']);
        $contact = Contact::factory()->hasAttached($vacancy)->createOne(['name' => 'Олена Коваль']);

        Livewire::test(ManageContacts::class)
            ->callAction(TestAction::make('delete')->table($contact))
            ->assertNotified();

        $this->assertModelMissing($contact);
        $this->assertModelExists($vacancy);
        $this->assertSame([], $vacancy->contacts()->pluck('contacts.id')->all());
    }

    /**
     * @return void
     */
    public function testTableIsSortedByName(): void
    {
        $pavlo = Contact::factory()->createOne(['name' => 'Павло']);
        $andrii = Contact::factory()->createOne(['name' => 'Андрій']);
        $olena = Contact::factory()->createOne(['name' => 'Олена']);

        Livewire::test(ManageContacts::class)
            ->assertCanSeeTableRecords([$andrii, $olena, $pavlo], inOrder: true);
    }

    /**
     * @return void
     */
    public function testSearchFiltersByName(): void
    {
        $olena = Contact::factory()->createOne(['name' => 'Олена Коваль']);
        $andrii = Contact::factory()->createOne(['name' => 'Андрій Мельник']);

        Livewire::test(ManageContacts::class)
            ->searchTable('Коваль')
            ->assertCanSeeTableRecords([$olena])
            ->assertCanNotSeeTableRecords([$andrii]);
    }

    /**
     * @return void
     */
    public function testCreateFormShowsSourcesRightUnderNameWithoutSection(): void
    {
        Livewire::test(ManageContacts::class)
            ->mountAction(TestAction::make('create'))
            ->assertSchemaComponentExists('name')
            ->assertSchemaComponentExists(
                'sources',
                checkComponentUsing: static fn (Repeater $repeater): bool => null === $repeater->getContainer()->getParentComponent()
                    && 'Додати джерело' === $repeater->getAddActionLabel(),
            );
    }

    /**
     * @return void
     */
    public function testCreatesContactWithNameOnly(): void
    {
        Livewire::test(ManageContacts::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'Олена Коваль'])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $contact = Contact::query()->where('name', 'Олена Коваль')->firstOrFail();

        $this->assertDatabaseMissing('contact_vacancy_source', ['contact_id' => $contact->id]);
    }

    /**
     * @return void
     */
    public function testCreateRejectsEmptyName(): void
    {
        Livewire::test(ManageContacts::class)
            ->callAction(TestAction::make('create'), data: ['name' => ''])
            ->assertHasFormErrors(['name' => 'required']);

        $this->assertDatabaseCount(Contact::class, 0);
    }

    /**
     * @return void
     */
    public function testCreateAllowsDuplicateName(): void
    {
        Contact::factory()->createOne(['name' => 'Олена Коваль']);

        Livewire::test(ManageContacts::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'Олена Коваль'])
            ->assertHasNoFormErrors();

        $this->assertSame(2, Contact::query()->where('name', 'Олена Коваль')->count());
    }

    /**
     * @return void
     */
    public function testCreatesContactWithTwoSourcesAndTheirLinks(): void
    {
        $linkedIn = VacancySource::factory()->createOne(['name' => 'LinkedIn']);
        $telegram = VacancySource::factory()->createOne(['name' => 'Telegram']);

        Livewire::test(ManageContacts::class)
            ->callAction(TestAction::make('create'), data: [
                'name' => 'Олена Коваль',
                'sources' => [
                    ['vacancy_source_id' => $linkedIn->id, 'url' => 'https://www.linkedin.com/in/olena-koval/'],
                    ['vacancy_source_id' => $telegram->id, 'url' => 'https://t.me/olena_koval'],
                ],
            ])
            ->assertHasNoFormErrors();

        $contact = Contact::query()->where('name', 'Олена Коваль')->firstOrFail();

        $this->assertDatabaseCount('contact_vacancy_source', 2);
        $this->assertDatabaseHas('contact_vacancy_source', ['contact_id' => $contact->id, 'vacancy_source_id' => $linkedIn->id, 'url' => 'https://www.linkedin.com/in/olena-koval/']);
        $this->assertDatabaseHas('contact_vacancy_source', ['contact_id' => $contact->id, 'vacancy_source_id' => $telegram->id, 'url' => 'https://t.me/olena_koval']);
    }

    /**
     * @return void
     */
    public function testCreatesSourceWithoutLink(): void
    {
        $linkedIn = VacancySource::factory()->createOne(['name' => 'LinkedIn']);

        Livewire::test(ManageContacts::class)
            ->callAction(TestAction::make('create'), data: [
                'name' => 'Олена Коваль',
                'sources' => [['vacancy_source_id' => $linkedIn->id, 'url' => '']],
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('contact_vacancy_source', ['vacancy_source_id' => $linkedIn->id, 'url' => null]);
    }

    /**
     * @return void
     */
    public function testCreateRejectsDuplicateSource(): void
    {
        $linkedIn = VacancySource::factory()->createOne(['name' => 'LinkedIn']);

        Livewire::test(ManageContacts::class)
            ->callAction(TestAction::make('create'), data: [
                'name' => 'Олена Коваль',
                'sources' => [
                    ['vacancy_source_id' => $linkedIn->id, 'url' => 'https://www.linkedin.com/in/olena-koval/'],
                    ['vacancy_source_id' => $linkedIn->id, 'url' => null],
                ],
            ])
            ->assertHasFormErrors(['sources.1.vacancy_source_id']);

        $this->assertDatabaseCount(Contact::class, 0);
        $this->assertDatabaseCount('contact_vacancy_source', 0);
    }

    /**
     * @return void
     */
    public function testCreateRejectsLinkWithoutSource(): void
    {
        Livewire::test(ManageContacts::class)
            ->callAction(TestAction::make('create'), data: [
                'name' => 'Олена Коваль',
                'sources' => [['vacancy_source_id' => null, 'url' => 'https://t.me/olena_koval']],
            ])
            ->assertHasFormErrors(['sources.0.vacancy_source_id']);

        $this->assertDatabaseCount(Contact::class, 0);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidSourceLinkProvider(): array
    {
        return [
            'without scheme' => ['t.me/olena_koval'],
            'with ftp scheme' => ['ftp://example.com/olena'],
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
        $telegram = VacancySource::factory()->createOne(['name' => 'Telegram']);

        Livewire::test(ManageContacts::class)
            ->callAction(TestAction::make('create'), data: [
                'name' => 'Олена Коваль',
                'sources' => [['vacancy_source_id' => $telegram->id, 'url' => $url]],
            ])
            ->assertHasFormErrors(['sources.0.url']);

        $this->assertDatabaseCount(Contact::class, 0);
    }

    /**
     * @return void
     */
    public function testCreatesSourceFromContactFormAndSelectsIt(): void
    {
        $component = Livewire::test(ManageContacts::class)
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
    public function testEditShowsCurrentNameAndSources(): void
    {
        $linkedIn = VacancySource::factory()->createOne(['name' => 'LinkedIn']);
        $contact = Contact::factory()
            ->hasAttached($linkedIn, ['url' => 'https://www.linkedin.com/in/olena-koval/'])
            ->createOne(['name' => 'Олена Коваль']);

        Livewire::test(ManageContacts::class)
            ->mountAction(TestAction::make('edit')->table($contact))
            ->assertSchemaStateSet([
                'name' => 'Олена Коваль',
                'sources' => [['vacancy_source_id' => $linkedIn->id, 'url' => 'https://www.linkedin.com/in/olena-koval/']],
            ]);
    }

    /**
     * @return void
     */
    public function testEditRemovesSourceRow(): void
    {
        $linkedIn = VacancySource::factory()->createOne(['name' => 'LinkedIn']);
        $telegram = VacancySource::factory()->createOne(['name' => 'Telegram']);
        $contact = Contact::factory()
            ->hasAttached($linkedIn, ['url' => 'https://www.linkedin.com/in/olena-koval/'])
            ->hasAttached($telegram, ['url' => 'https://t.me/olena_koval'])
            ->createOne(['name' => 'Олена Коваль']);

        Livewire::test(ManageContacts::class)
            ->callAction(TestAction::make('edit')->table($contact), data: ['sources' => [
                ['vacancy_source_id' => $linkedIn->id, 'url' => 'https://www.linkedin.com/in/olena-koval/'],
            ]])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertSame([$linkedIn->id], $contact->vacancySources()->pluck('vacancy_sources.id')->all());
        $this->assertDatabaseHas('contact_vacancy_source', ['contact_id' => $contact->id, 'vacancy_source_id' => $linkedIn->id, 'url' => 'https://www.linkedin.com/in/olena-koval/']);
        $this->assertModelExists($telegram);
    }

    /**
     * @return void
     */
    public function testDeletesSingleContact(): void
    {
        $contact = Contact::factory()->hasAttached(VacancySource::factory(), ['url' => null])->createOne();
        $other = Contact::factory()->createOne();

        Livewire::test(ManageContacts::class)
            ->callAction(TestAction::make('delete')->table($contact))
            ->assertNotified();

        $this->assertModelMissing($contact);
        $this->assertModelExists($other);
        $this->assertDatabaseMissing('contact_vacancy_source', ['contact_id' => $contact->id]);
    }

    /**
     * @return void
     */
    public function testBulkDeletesSelectedContacts(): void
    {
        $olena = Contact::factory()->createOne(['name' => 'Олена']);
        $andrii = Contact::factory()->createOne(['name' => 'Андрій']);
        $pavlo = Contact::factory()->createOne(['name' => 'Павло']);

        Livewire::test(ManageContacts::class)
            ->selectTableRecords([$olena, $andrii])
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertNotified();

        $this->assertModelMissing($olena);
        $this->assertModelMissing($andrii);
        $this->assertModelExists($pavlo);
    }
}
