<?php

namespace App\Containers\AppSection\Vacancy\Tests\Functional\Filament\Resources\Vacancies\Pages;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\Vacancy\Actions\CreateVacancyNoteAction;
use App\Containers\AppSection\Vacancy\Actions\DeleteVacancyNoteAction;
use App\Containers\AppSection\Vacancy\Actions\UpdateVacancyNoteAction;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Models\VacancyNote;
use App\Containers\AppSection\Vacancy\Tests\FunctionalTestCase;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Pages\ManageVacancyNotes;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Schemas\VacancyNoteForm;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\Tables\VacancyNotesTable;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\VacancyResource;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Carbon;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ManageVacancyNotes::class)]
#[CoversClass(VacancyNoteForm::class)]
#[CoversClass(VacancyNotesTable::class)]
#[CoversClass(CreateVacancyNoteAction::class)]
#[CoversClass(UpdateVacancyNoteAction::class)]
#[CoversClass(DeleteVacancyNoteAction::class)]
final class ManageVacancyNotesTest extends FunctionalTestCase
{
    /**
     * @var Vacancy
     */
    private Vacancy $vacancy;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->vacancy = Vacancy::factory()->createOne(['title' => 'PHP Developer']);
    }

    /**
     * @return void
     */
    public function testSubNavigationLeadsToActiveNotesPage(): void
    {
        $this->actingAs(User::factory()->createOne());
        $notesUrl = VacancyResource::getUrl('notes', ['record' => $this->vacancy]);

        $this->assertSame(url("/admin/vacancies/{$this->vacancy->id}/notes"), $notesUrl);

        $this->get(VacancyResource::getUrl('view', ['record' => $this->vacancy]))
            ->assertOk()
            ->assertSee('href="' . $notesUrl . '"', false);

        $this->get($notesUrl)
            ->assertOk()
            ->assertSeeInOrder(['fi-page-sub-navigation-tabs', 'Огляд', 'aria-current="page"', 'Нотатки'], false);
    }

    /**
     * @return void
     */
    public function testTitleAndBreadcrumbsContainVacancyTitle(): void
    {
        $this->actingAs(User::factory()->createOne());

        /** @var ManageVacancyNotes $page */
        $page = $this->notesPage()->instance();

        $this->assertStringContainsString('PHP Developer', $page->getTitle());
        $this->assertContains('PHP Developer', $page->getBreadcrumbs());
    }

    /**
     * @return void
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get("/admin/vacancies/{$this->vacancy->id}/notes")
            ->assertRedirect('/admin/login');
    }

    /**
     * @return void
     */
    public function testMissingVacancyIsNotFound(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->get('/admin/vacancies/999999/notes')
            ->assertNotFound();
    }

    /**
     * @return void
     */
    public function testShowsOnlyNotesOfThisVacancy(): void
    {
        $this->actingAs(User::factory()->createOne());
        $letter = VacancyNote::factory()->createOne(['vacancy_id' => $this->vacancy->id, 'title' => 'Лист від рекрутера']);
        $foreign = VacancyNote::factory()->createOne(['title' => 'Тестове завдання']);

        $this->notesPage()
            ->assertCanSeeTableRecords([$letter])
            ->assertCanNotSeeTableRecords([$foreign]);
    }

    /**
     * @return void
     */
    public function testCardShowsTitleDateAndFormattedBody(): void
    {
        $this->actingAs(User::factory()->createOne());
        VacancyNote::factory()->createOne([
            'vacancy_id' => $this->vacancy->id,
            'title' => 'Результат співбесіди з HR',
            'occurred_at' => '2026-11-20 15:30:00',
            'body' => '<p>Пройшла, далі <strong>технічне інтерв\'ю</strong></p>',
        ]);

        $this->notesPage()
            ->assertSeeInOrder(['Результат співбесіди з HR', '20.11.2026 15:30'])
            ->assertSeeHtml('<strong>технічне інтерв&#039;ю</strong>');
    }

    /**
     * @return void
     */
    public function testCardsAreOrderedByEventDateThenNewestAdded(): void
    {
        $this->actingAs(User::factory()->createOne());
        $interview = VacancyNote::factory()->createOne(['vacancy_id' => $this->vacancy->id, 'title' => 'Технічна співбесіда', 'occurred_at' => '2026-11-22 10:00:00']);
        $letter = VacancyNote::factory()->createOne(['vacancy_id' => $this->vacancy->id, 'title' => 'Лист від рекрутера', 'occurred_at' => '2026-11-18 10:00:00']);
        $sameTimeEarlier = VacancyNote::factory()->createOne(['vacancy_id' => $this->vacancy->id, 'title' => 'Перша нотатка', 'occurred_at' => '2026-11-18 10:00:00']);
        $sameTimeLater = VacancyNote::factory()->createOne(['vacancy_id' => $this->vacancy->id, 'title' => 'Друга нотатка', 'occurred_at' => '2026-11-18 10:00:00']);

        $this->notesPage()
            ->assertCanSeeTableRecords([$interview, $sameTimeLater, $sameTimeEarlier, $letter], inOrder: true);
    }

    /**
     * @return void
     */
    public function testEmptyStateOffersToAddNote(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->notesPage()
            ->assertSee('Нотаток ще немає')
            ->assertActionVisible(TestAction::make('create')->table());
    }

    /**
     * @return void
     */
    public function testNotesArePaginated(): void
    {
        $this->actingAs(User::factory()->createOne());
        $notes = collect(range(1, 15))->map(fn (int $day): VacancyNote => VacancyNote::factory()->createOne([
            'vacancy_id' => $this->vacancy->id,
            'occurred_at' => sprintf('2026-11-%02d 10:00:00', $day),
        ]));
        $newest = $notes->sortByDesc('occurred_at')->values();
        $page = $this->notesPage();

        /** @var ManageVacancyNotes $instance */
        $instance = $page->instance();

        $page
            ->assertCountTableRecords(15)
            ->assertCanSeeTableRecords($newest->take(10))
            ->assertCanNotSeeTableRecords($newest->slice(10))
            ->call('gotoPage', 2, $instance->getTablePaginationPageName())
            ->assertCanSeeTableRecords($newest->slice(10))
            ->assertCanNotSeeTableRecords($newest->take(10));
    }

    /**
     * @return void
     */
    public function testCreatesNoteForPageVacancy(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->notesPage()
            ->callAction(TestAction::make('create'), data: [
                'title' => 'Результат співбесіди з HR',
                'occurred_at' => '2026-11-20 15:30',
                'body' => '<p>Питання:</p><ul><li><p>Про <strong>SOLID</strong></p></li></ul>',
            ])
            ->assertHasNoFormErrors()
            ->assertActionNotMounted(TestAction::make('create'))
            ->assertSee('Результат співбесіди з HR');

        $note = VacancyNote::query()->sole();

        $this->assertSame($this->vacancy->id, $note->vacancy_id);
        $this->assertSame('Результат співбесіди з HR', $note->title);
        $this->assertSame('2026-11-20 15:30', $note->occurred_at->format('Y-m-d H:i'));
        $this->assertStringContainsString('<strong>SOLID</strong>', $note->body);
        $this->assertStringContainsString('<ul>', $note->body);
    }

    /**
     * @return void
     */
    public function testEventDateDefaultsToNow(): void
    {
        $this->actingAs(User::factory()->createOne());
        Carbon::setTestNow('2026-11-24 10:00:00');

        $this->notesPage()
            ->mountAction(TestAction::make('create'))
            ->assertSchemaStateSet(['occurred_at' => '2026-11-24 10:00']);
    }

    /**
     * @return void
     */
    public function testEventInThePastIsSaved(): void
    {
        $this->actingAs(User::factory()->createOne());
        Carbon::setTestNow('2026-11-24 10:00:00');

        $this->notesPage()
            ->callAction(TestAction::make('create'), data: [...$this->validData(), 'occurred_at' => '2026-11-21 14:00'])
            ->assertHasNoFormErrors();

        $this->assertSame('2026-11-21 14:00', VacancyNote::query()->sole()->occurred_at->format('Y-m-d H:i'));
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function invalidDataProvider(): array
    {
        return [
            'missing title' => [['title' => ''], 'title', 'required'],
            'too long title' => [['title' => str_repeat('a', 256)], 'title', 'max'],
            'missing event date' => [['occurred_at' => null], 'occurred_at', 'required'],
        ];
    }

    /**
     * @param array<string, mixed> $override
     * @param string $field
     * @param string $rule
     *
     * @return void
     */
    #[DataProvider('invalidDataProvider')]
    public function testInvalidNoteIsNotCreated(array $override, string $field, string $rule): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->notesPage()
            ->callAction(TestAction::make('create'), data: [...$this->validData(), ...$override])
            ->assertHasFormErrors([$field => $rule]);

        $this->assertDatabaseCount(VacancyNote::class, 0);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function emptyBodyProvider(): array
    {
        return [
            'empty editor' => [''],
            'only unsafe markup' => ['<p><script>alert(1)</script></p>'],
        ];
    }

    /**
     * @param string $body
     *
     * @return void
     */
    #[DataProvider('emptyBodyProvider')]
    public function testEmptyBodyIsRejected(string $body): void
    {
        $this->actingAs(User::factory()->createOne());

        $messages = $this->notesPage()
            ->callAction(TestAction::make('create'), data: [...$this->validData(), 'body' => $body])
            ->assertHasFormErrors(['body'])
            ->errors()
            ->all();

        $this->assertContains('Поле текст є обов’язковим.', $messages);

        $this->assertDatabaseCount(VacancyNote::class, 0);
    }

    /**
     * @return void
     */
    public function testUnsafeMarkupIsRemovedFromBody(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->notesPage()
            ->callAction(TestAction::make('create'), data: [...$this->validData(), 'body' => '<p>Питання про SOLID</p><script>alert(1)</script>'])
            ->assertHasNoFormErrors();

        $body = VacancyNote::query()->sole()->body;

        $this->assertStringContainsString('<p>Питання про SOLID</p>', $body);
        $this->assertStringNotContainsString('<script>', $body);
    }

    /**
     * @return void
     */
    public function testFormsOpenInSlideOver(): void
    {
        $this->actingAs(User::factory()->createOne());
        $note = VacancyNote::factory()->createOne(['vacancy_id' => $this->vacancy->id]);

        $this->notesPage()
            ->assertActionExists(TestAction::make('create'), static fn (CreateAction $action): bool => $action->isModalSlideOver())
            ->assertActionExists(TestAction::make('edit')->table($note), static fn (EditAction $action): bool => $action->isModalSlideOver());
    }

    /**
     * @return void
     */
    public function testEditShowsCurrentValuesAndUpdatesBody(): void
    {
        $this->actingAs(User::factory()->createOne());
        $note = VacancyNote::factory()->createOne([
            'vacancy_id' => $this->vacancy->id,
            'title' => 'Технічна співбесіда',
            'occurred_at' => '2026-11-22 11:00:00',
            'body' => '<p>Питання про індекси</p>',
        ]);

        $this->notesPage()
            ->mountAction(TestAction::make('edit')->table($note))
            ->assertSchemaStateSet(['title' => 'Технічна співбесіда'])
            ->fillForm(['body' => '<p>Питання про індекси</p><p>Відповів про B-tree</p>'])
            ->callMountedAction()
            ->assertHasNoFormErrors()
            ->assertSee('Відповів про B-tree');

        $note->refresh();

        $this->assertSame('Технічна співбесіда', $note->title);
        $this->assertSame($this->vacancy->id, $note->vacancy_id);
        $this->assertStringContainsString('Відповів про B-tree', $note->body);
    }

    /**
     * @return void
     */
    public function testEditRejectsClearedTitle(): void
    {
        $this->actingAs(User::factory()->createOne());
        $note = VacancyNote::factory()->createOne(['vacancy_id' => $this->vacancy->id, 'title' => 'Технічна співбесіда']);

        $this->notesPage()
            ->callAction(TestAction::make('edit')->table($note), data: ['title' => ''])
            ->assertHasFormErrors(['title' => 'required']);

        $this->assertSame('Технічна співбесіда', $note->refresh()->title);
    }

    /**
     * @return void
     */
    public function testDeletesOnlySelectedNote(): void
    {
        $this->actingAs(User::factory()->createOne());
        $letter = VacancyNote::factory()->createOne(['vacancy_id' => $this->vacancy->id, 'title' => 'Лист від рекрутера']);
        $interview = VacancyNote::factory()->createOne(['vacancy_id' => $this->vacancy->id, 'title' => 'Технічна співбесіда']);

        $this->notesPage()
            ->callAction(TestAction::make('delete')->table($letter))
            ->assertCanNotSeeTableRecords([$letter])
            ->assertCanSeeTableRecords([$interview]);

        $this->assertModelMissing($letter);
        $this->assertModelExists($interview);
        $this->assertModelExists($this->vacancy);
    }

    /**
     * @return void
     */
    public function testCancellingDeleteKeepsNote(): void
    {
        $this->actingAs(User::factory()->createOne());
        $note = VacancyNote::factory()->createOne(['vacancy_id' => $this->vacancy->id]);

        $this->notesPage()
            ->mountAction(TestAction::make('delete')->table($note))
            ->assertActionMounted(TestAction::make('delete')->table($note))
            ->unmountAction();

        $this->assertModelExists($note);
    }

    /**
     * @return Testable
     */
    private function notesPage(): Testable
    {
        return Livewire::test(ManageVacancyNotes::class, ['record' => $this->vacancy->id]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validData(): array
    {
        return [
            'title' => 'Лист від рекрутера',
            'occurred_at' => '2026-11-20 15:30',
            'body' => '<p>Уточнення щодо вакансії</p>',
        ];
    }
}
