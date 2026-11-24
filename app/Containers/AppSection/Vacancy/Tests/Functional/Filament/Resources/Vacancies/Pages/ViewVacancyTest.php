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
use App\Containers\AppSection\Vacancy\Models\VacancyNote;
use App\Containers\AppSection\Vacancy\Tests\FunctionalTestCase;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Pages\ViewVacancy;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Schemas\VacancyInfolist;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\VacancyResource;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Repeater;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ViewVacancy::class)]
#[CoversClass(VacancyInfolist::class)]
final class ViewVacancyTest extends FunctionalTestCase
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
    public function testSubNavigationHasActiveOverviewAndNotesItems(): void
    {
        $vacancy = Vacancy::factory()->createOne(['title' => 'PHP Developer']);

        $this->get(VacancyResource::getUrl('view', ['record' => $vacancy]))
            ->assertOk()
            ->assertSeeInOrder(['fi-page-sub-navigation-tabs', 'aria-current="page"', 'Огляд', VacancyResource::getUrl('notes', ['record' => $vacancy]), 'Нотатки'], false)
            ->assertDontSee('fi-page-sub-navigation-sidebar', false)
            ->assertDontSee('Редагування');
    }

    /**
     * @return void
     */
    public function testTitleAndBreadcrumbsContainVacancyTitle(): void
    {
        $vacancy = Vacancy::factory()->createOne(['title' => 'PHP Developer']);

        /** @var ViewVacancy $page */
        $page = Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])->instance();

        $this->assertStringContainsString('PHP Developer', (string) $page->getTitle());
        $this->assertContains('PHP Developer', $page->getBreadcrumbs());
    }

    /**
     * @return void
     */
    public function testShowsAllAttributes(): void
    {
        $vacancy = Vacancy::factory()
            ->hasAttached(VacancySource::factory()->state(['name' => 'DOU']), ['url' => 'https://jobs.dou.ua/companies/macpaw/vacancies/123/'])
            ->createOne([
                'title' => 'Senior PHP Developer',
                'company_id' => Company::factory()->createOne(['name' => 'MacPaw'])->id,
                'status_id' => Status::factory()->createOne(['name' => 'Applied', 'color' => StatusColor::BLUE])->id,
                'description' => '<p>Ми шукаємо <strong>PHP</strong>-розробника</p><ul><li>Laravel</li></ul><script>alert(1)</script>',
                'location' => 'Київ',
                'work_format' => WorkFormat::HYBRID,
                'salary_min' => 4000,
                'salary_max' => 5500,
                'currency' => Currency::USD,
                'applied_at' => '2026-11-20',
                'next_action_at' => '2026-11-25 10:00:00',
            ]);

        $page = Livewire::test(ViewVacancy::class, ['record' => $vacancy->id]);

        $this->assertMatchesRegularExpression('/fi-section-header-heading"\s*>\s*Опис\s*</u', $page->html());
        $this->assertDoesNotMatchRegularExpression('/fi-section-header-heading"\s*>\s*Нотатки\s*</u', $page->html());

        $page
            ->assertSee(['Senior PHP Developer', 'MacPaw', 'DOU', 'Київ', 'Гібрид', '4 000–5 500 USD', '20.11.2026', '25.11.2026 10:00'])
            ->assertSeeHtml(StatusColor::BLUE->renderBadge('Applied')->toHtml())
            ->assertSeeHtml('href="https://jobs.dou.ua/companies/macpaw/vacancies/123/"')
            ->assertSeeHtml('target="_blank"')
            ->assertSeeHtml('<p>Ми шукаємо <strong>PHP</strong>-розробника</p><ul><li>Laravel</li></ul>')
            ->assertDontSeeHtml('<script>alert(1)</script>');
    }

    /**
     * @return void
     */
    public function testShowsSourcesWithLinksOpeningInNewTab(): void
    {
        $vacancy = Vacancy::factory()
            ->hasAttached(VacancySource::factory()->state(['name' => 'DOU']), ['url' => 'https://jobs.dou.ua/vacancies/123/'])
            ->hasAttached(VacancySource::factory()->state(['name' => 'LinkedIn']), ['url' => null])
            ->createOne();

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->assertSeeInOrder(['DOU', 'https://jobs.dou.ua/vacancies/123/', 'LinkedIn'])
            ->assertSeeHtml('href="https://jobs.dou.ua/vacancies/123/"');

        $html = (string) $this->get(VacancyResource::getUrl('view', ['record' => $vacancy]))->getContent();

        $this->assertSame(1, substr_count($html, 'target="_blank"'));
        $this->assertSame(1, substr_count($html, 'href="https://'));
    }

    /**
     * @return void
     */
    public function testShowsDashForEmptyAttributes(): void
    {
        $vacancy = Vacancy::factory()->createOne();

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->assertSee('—');
    }

    /**
     * @return void
     */
    public function testEditActionOpensSlideOverWithCurrentValues(): void
    {
        $applied = Status::factory()->createOne(['name' => 'Applied']);
        $vacancy = Vacancy::factory()->createOne(['title' => 'PHP Developer', 'status_id' => $applied->id]);

        $component = Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->mountAction(TestAction::make('edit'))
            ->assertActionMounted(TestAction::make('edit'))
            ->assertSchemaStateSet(['title' => 'PHP Developer', 'status_id' => $applied->id]);

        /** @var ViewVacancy $page */
        $page = $component->instance();

        $this->assertTrue((bool) $page->getMountedAction()?->isModalSlideOver());
    }

    /**
     * @return void
     */
    public function testEditChangesStatusAndRefreshesOverview(): void
    {
        $applied = Status::factory()->createOne(['name' => 'Applied', 'color' => StatusColor::BLUE]);
        $interview = Status::factory()->createOne(['name' => 'Interview', 'color' => StatusColor::AMBER]);
        $vacancy = Vacancy::factory()->createOne(['title' => 'PHP Developer', 'status_id' => $applied->id]);

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->callAction(TestAction::make('edit'), data: ['status_id' => $interview->id])
            ->assertHasNoFormErrors()
            ->assertActionNotMounted(TestAction::make('edit'))
            ->assertNoRedirect()
            ->assertSeeHtml(StatusColor::AMBER->renderBadge('Interview')->toHtml())
            ->assertDontSeeHtml(StatusColor::BLUE->renderBadge('Applied')->toHtml());

        $this->assertDatabaseHas(Vacancy::class, ['id' => $vacancy->id, 'status_id' => $interview->id]);
    }

    /**
     * @return void
     */
    public function testEditShowsCurrentSourcesAndKeepsTheirLinksOnResave(): void
    {
        $dou = VacancySource::factory()->createOne(['name' => 'DOU']);
        $linkedIn = VacancySource::factory()->createOne(['name' => 'LinkedIn']);
        $vacancy = Vacancy::factory()
            ->hasAttached($dou, ['url' => 'https://jobs.dou.ua/vacancies/123/'])
            ->hasAttached($linkedIn, ['url' => null])
            ->createOne();

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->mountAction(TestAction::make('edit'))
            ->assertSchemaStateSet(['sources' => [
                ['vacancy_source_id' => $dou->id, 'url' => 'https://jobs.dou.ua/vacancies/123/'],
                ['vacancy_source_id' => $linkedIn->id, 'url' => null],
            ]])
            ->callMountedAction()
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount('vacancy_vacancy_source', 2);
        $this->assertDatabaseHas('vacancy_vacancy_source', ['vacancy_id' => $vacancy->id, 'vacancy_source_id' => $dou->id, 'url' => 'https://jobs.dou.ua/vacancies/123/']);
        $this->assertDatabaseHas('vacancy_vacancy_source', ['vacancy_id' => $vacancy->id, 'vacancy_source_id' => $linkedIn->id, 'url' => null]);
    }

    /**
     * @return void
     */
    public function testEditRemovesSourceRow(): void
    {
        $dou = VacancySource::factory()->createOne(['name' => 'DOU']);
        $linkedIn = VacancySource::factory()->createOne(['name' => 'LinkedIn']);
        $vacancy = Vacancy::factory()
            ->hasAttached($dou, ['url' => 'https://jobs.dou.ua/vacancies/123/'])
            ->hasAttached($linkedIn, ['url' => 'https://www.linkedin.com/jobs/view/456/'])
            ->createOne();

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->callAction(TestAction::make('edit'), data: ['sources' => [
                ['vacancy_source_id' => $dou->id, 'url' => 'https://jobs.dou.ua/vacancies/123/'],
            ]])
            ->assertHasNoFormErrors()
            ->assertDontSee('LinkedIn');

        $this->assertSame([$dou->id], $vacancy->vacancySources()->pluck('vacancy_sources.id')->all());
        $this->assertModelExists($linkedIn);
    }

    /**
     * @return void
     */
    public function testEditRemovingAllSourceRowsLeavesVacancyWithoutSources(): void
    {
        $vacancy = Vacancy::factory()
            ->hasAttached(VacancySource::factory()->count(2), ['url' => null])
            ->createOne();

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->callAction(TestAction::make('edit'), data: ['sources' => []])
            ->assertHasNoFormErrors();

        $this->assertModelExists($vacancy);
        $this->assertDatabaseMissing('vacancy_vacancy_source', ['vacancy_id' => $vacancy->id]);
    }

    /**
     * @return void
     */
    public function testEditRejectsClearedTitle(): void
    {
        $vacancy = Vacancy::factory()->createOne(['title' => 'PHP Developer']);

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->callAction(TestAction::make('edit'), data: ['title' => ''])
            ->assertHasFormErrors(['title' => 'required']);

        $this->assertDatabaseHas(Vacancy::class, ['id' => $vacancy->id, 'title' => 'PHP Developer']);
    }

    /**
     * @return void
     */
    public function testEditStoresFormattedDescription(): void
    {
        $vacancy = Vacancy::factory()->createOne();

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->callAction(TestAction::make('edit'), data: ['description' => '<p>Стек: <strong>Laravel</strong></p>'])
            ->assertHasNoFormErrors()
            ->assertSeeHtml('<strong>Laravel</strong>');

        $this->assertSame('<p>Стек: <strong>Laravel</strong></p>', $vacancy->refresh()->description);
    }

    /**
     * @return void
     */
    public function testShowsContactsWithSourceLinksOpeningInNewTab(): void
    {
        $contact = Contact::factory()
            ->hasAttached(VacancySource::factory()->state(['name' => 'LinkedIn']), ['url' => 'https://www.linkedin.com/in/olena-koval/'])
            ->hasAttached(VacancySource::factory()->state(['name' => 'Telegram']), ['url' => null])
            ->createOne(['name' => 'Олена Коваль']);
        $vacancy = Vacancy::factory()->hasAttached($contact)->createOne();

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->assertSeeInOrder(['Контакти', 'Олена Коваль', 'LinkedIn', 'https://www.linkedin.com/in/olena-koval/', 'Telegram', 'Умови'])
            ->assertSeeHtml('href="https://www.linkedin.com/in/olena-koval/"');

        $html = (string) $this->get(VacancyResource::getUrl('view', ['record' => $vacancy]))->getContent();

        $this->assertSame(1, substr_count($html, 'target="_blank"'));
        $this->assertSame(1, substr_count($html, 'href="https://'));
    }

    /**
     * @return void
     */
    public function testShowsDashForVacancyWithoutContacts(): void
    {
        $vacancy = Vacancy::factory()
            ->hasAttached(VacancySource::factory()->state(['name' => 'DOU']), ['url' => 'https://jobs.dou.ua/vacancies/123/'])
            ->createOne(['salary_min' => 4000, 'salary_max' => 5500, 'currency' => Currency::USD]);

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->assertSeeInOrder(['DOU', 'Контакти', '—', 'Умови', '4 000–5 500 USD']);
    }

    /**
     * @return void
     */
    public function testEditShowsCurrentContacts(): void
    {
        $olena = Contact::factory()->createOne(['name' => 'Олена Коваль']);
        $andrii = Contact::factory()->createOne(['name' => 'Андрій Мельник']);
        $vacancy = Vacancy::factory()->hasAttached($olena)->hasAttached($andrii)->createOne();

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->mountAction(TestAction::make('edit'))
            ->assertSchemaStateSet(['contacts' => [$andrii->id, $olena->id]]);
    }

    /**
     * @return void
     */
    public function testEditRemovesContactAndKeepsItInDirectory(): void
    {
        $olena = Contact::factory()->createOne(['name' => 'Олена Коваль']);
        $andrii = Contact::factory()->createOne(['name' => 'Андрій Мельник']);
        $vacancy = Vacancy::factory()->hasAttached($olena)->hasAttached($andrii)->createOne();

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->callAction(TestAction::make('edit'), data: ['contacts' => [$olena->id]])
            ->assertHasNoFormErrors()
            ->assertDontSee('Андрій Мельник');

        $this->assertSame([$olena->id], $vacancy->contacts()->pluck('contacts.id')->all());
        $this->assertModelExists($andrii);
    }

    /**
     * @return void
     */
    public function testDeletingVacancyKeepsItsContacts(): void
    {
        $contact = Contact::factory()->createOne(['name' => 'Олена Коваль']);
        $vacancy = Vacancy::factory()->hasAttached($contact)->createOne(['title' => 'PHP Developer']);

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->callAction(TestAction::make('delete'));

        $this->assertModelMissing($vacancy);
        $this->assertModelExists($contact);
        $this->assertDatabaseMissing('contact_vacancy', ['contact_id' => $contact->id]);
    }

    /**
     * @return void
     */
    public function testDeletingVacancyDeletesOnlyItsNotes(): void
    {
        $vacancy = Vacancy::factory()->createOne(['title' => 'PHP Developer']);
        $notes = VacancyNote::factory()->count(2)->create(['vacancy_id' => $vacancy->id]);
        $foreignNote = VacancyNote::factory()->createOne();

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->callAction(TestAction::make('delete'));

        $this->assertModelMissing($vacancy);
        $notes->each(fn (VacancyNote $note) => $this->assertModelMissing($note));
        $this->assertModelExists($foreignNote);
    }

    /**
     * @return void
     */
    public function testDeletesVacancyAndRedirectsToTable(): void
    {
        $vacancy = Vacancy::factory()->createOne();

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->callAction(TestAction::make('delete'))
            ->assertRedirect(VacancyResource::getUrl('index'));

        $this->assertModelMissing($vacancy);
    }

    /**
     * @return void
     */
    public function testCancellingDeleteKeepsVacancy(): void
    {
        $vacancy = Vacancy::factory()->createOne();

        Livewire::test(ViewVacancy::class, ['record' => $vacancy->id])
            ->mountAction(TestAction::make('delete'))
            ->assertActionMounted(TestAction::make('delete'))
            ->unmountAction()
            ->assertNoRedirect();

        $this->assertModelExists($vacancy);
    }
}
