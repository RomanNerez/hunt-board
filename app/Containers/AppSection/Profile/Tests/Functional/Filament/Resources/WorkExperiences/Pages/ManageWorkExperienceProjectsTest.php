<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Resources\WorkExperiences\Pages;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Pages\ManageWorkExperienceProjects;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Pages\ManageWorkExperiences;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Schemas\ProjectForm;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Tables\ProjectsTable;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\User\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\Exceptions\ActionNotResolvableException;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\RichEditor;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ManageWorkExperienceProjects::class)]
#[CoversClass(ProjectForm::class)]
#[CoversClass(ProjectsTable::class)]
final class ManageWorkExperienceProjectsTest extends FunctionalTestCase
{
    /**
     * @var User
     */
    private User $user;

    /**
     * @var ProfileWorkExperience
     */
    private ProfileWorkExperience $experience;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->createOne();
        $this->experience = ProfileWorkExperience::factory()->createOne([
            'user_id' => $this->user->id,
            'company_name' => ['uk' => 'VARTEQ Inc.'],
            'started_on' => '2020-08-29',
            'finished_on' => '2020-10-12',
        ]);
    }

    /**
     * @return void
     */
    public function testProjectsActionLeadsToProjectsPage(): void
    {
        $this->actingAs($this->user);

        Livewire::test(ManageWorkExperiences::class)
            ->assertActionHasUrl(TestAction::make('projects')->table($this->experience), url("/admin/profile/work-experience/{$this->experience->id}/projects"))
            ->assertActionHasLabel(TestAction::make('projects')->table($this->experience), 'Проєкти');
    }

    /**
     * @return void
     */
    public function testPageShowsRecordTitleWithWorkExperienceActiveInSubNavigation(): void
    {
        $this->actingAs($this->user);

        $this->get("/admin/profile/work-experience/{$this->experience->id}/projects")
            ->assertOk()
            ->assertSee('Проєкти: VARTEQ Inc.')
            ->assertSeeInOrder(['fi-page-sub-navigation-sidebar', 'Загальне', 'aria-current="page"', 'Досвід роботи', 'Навички'], false);
    }

    /**
     * @return void
     */
    public function testBreadcrumbsLeadThroughWorkExperienceRecord(): void
    {
        $this->actingAs($this->user);

        /** @var ManageWorkExperienceProjects $page */
        $page = Livewire::test(ManageWorkExperienceProjects::class, ['record' => $this->experience->id])->instance();

        $this->assertSame(['Профіль', 'Досвід роботи', 'VARTEQ Inc.', 'Проєкти'], array_values($page->getBreadcrumbs()));
    }

    /**
     * @return void
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get("/admin/profile/work-experience/{$this->experience->id}/projects")
            ->assertRedirect('/admin/login');
    }

    /**
     * @return void
     */
    public function testForeignWorkExperienceIsNotOpened(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->get("/admin/profile/work-experience/{$this->experience->id}/projects")
            ->assertNotFound();
    }

    /**
     * @return void
     */
    public function testCreatesFinishedProjectForPageRecord(): void
    {
        $this->actingAs($this->user);
        ProfileWorkExperience::factory()->createOne(['user_id' => $this->user->id]);

        $this->projectsPage()
            ->callAction(TestAction::make('create'), data: $this->validData())
            ->assertHasNoFormErrors()
            ->assertNotified();

        $project = ProfileProject::query()->sole();

        $this->assertSame($this->experience->id, $project->work_experience_id);
        $this->assertSame('Аукціон з продажу автомобілів', $project->name);
        $this->assertSame('2020-08-29', $project->started_on->toDateString());
        $this->assertSame('2020-10-12', $project->finished_on?->toDateString());
        $this->assertSame(['PostgreSQL', 'MySQL', 'jQuery'], $project->stacks()->pluck('name')->all());
        $this->assertNull($project->description);
    }

    /**
     * @return void
     */
    public function testCurrentProjectIsShownAsOngoing(): void
    {
        $this->actingAs($this->user);

        $this->projectsPage()
            ->callAction(TestAction::make('create'), data: [...$this->validData(), 'started_on' => '2020-09-01', 'finished_on' => null])
            ->assertHasNoFormErrors()
            ->assertSee('09.2020 — дотепер');

        $this->assertNull(ProfileProject::query()->sole()->finished_on);
    }

    /**
     * @return void
     */
    public function testDatesOutsideWorkPeriodAreSaved(): void
    {
        $this->actingAs($this->user);

        $this->projectsPage()
            ->callAction(TestAction::make('create'), data: [...$this->validData(), 'started_on' => '2019-06-01', 'finished_on' => '2021-10-01'])
            ->assertHasNoFormErrors();

        $project = ProfileProject::query()->sole();
        $this->assertSame('2019-06-01', $project->started_on->toDateString());
        $this->assertSame('2021-10-01', $project->finished_on?->toDateString());
    }

    /**
     * @return void
     */
    public function testDescriptionIsRichEditorAndKeepsFormatting(): void
    {
        $this->actingAs($this->user);
        $html = '<p>Міграція бази даних з MySQL на PostgreSQL</p><ul><li><p>Мігровано понад 10 млн записів</p></li><li><p>Прискорено вибірки завдяки індексам</p></li></ul>';

        $this->projectsPage()
            ->mountAction(TestAction::make('create'))
            ->assertFormFieldExists('description.uk', static fn (RichEditor $field): bool => !$field->isRequired() && 20000 === $field->getMaxLength())
            ->fillForm([...$this->validData(), 'description' => ['uk' => $html]])
            ->callMountedAction()
            ->assertHasNoFormErrors();

        $project = ProfileProject::query()->sole();
        $this->assertIsString($project->description);
        $this->assertStringContainsString('<ul>', $project->description);
        $this->assertSame(2, substr_count($project->description, '<li>'));
        $this->assertStringContainsString('Міграція бази даних з MySQL на PostgreSQL', $project->description);
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function invalidDataProvider(): array
    {
        return [
            'missing name' => [['name' => ['uk' => '']], 'name.uk', 'required'],
            'missing start date' => [['started_on' => null], 'started_on', 'required'],
            'too long name' => [['name' => ['uk' => str_repeat('a', 256)]], 'name.uk', 'max'],
            'finished before started' => [['started_on' => '2020-09-01', 'finished_on' => '2020-06-01'], 'finished_on', 'after_or_equal'],
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
    public function testInvalidProjectIsNotCreated(array $override, string $field, string $rule): void
    {
        $this->actingAs($this->user);

        $this->projectsPage()
            ->callAction(TestAction::make('create'), data: [...$this->validData(), ...$override])
            ->assertHasFormErrors([$field => $rule])
            ->assertNotNotified();

        $this->assertDatabaseCount(ProfileProject::class, 0);
    }

    /**
     * @return void
     */
    public function testValidationMessagesAreUkrainian(): void
    {
        $this->actingAs($this->user);

        $messages = $this->projectsPage()
            ->callAction(TestAction::make('create'), data: [...$this->validData(), 'name' => ['uk' => ''], 'started_on' => '2020-09-01', 'finished_on' => '2020-06-01'])
            ->errors()
            ->all();

        $this->assertContains('Поле назва проєкту є обов’язковим.', $messages);
        $this->assertContains('Поле дата завершення має містити дату не раніше дата початку.', $messages);
    }

    /**
     * @return void
     */
    public function testProjectFormOpensInSlideOver(): void
    {
        $this->actingAs($this->user);
        $project = $this->project();

        $this->projectsPage()
            ->assertActionExists(TestAction::make('create'), static fn (CreateAction $action): bool => $action->isModalSlideOver())
            ->assertActionExists(TestAction::make('edit')->table($project), static fn (EditAction $action): bool => $action->isModalSlideOver());
    }

    /**
     * @return void
     */
    public function testShowsOnlyRecordProjectsFromNewestStart(): void
    {
        $this->actingAs($this->user);
        $experience = ProfileWorkExperience::factory()->createOne(['user_id' => $this->user->id]);
        $cabinet = ProfileProject::factory()->createOne(['work_experience_id' => $experience->id, 'name' => 'Особистий кабінет клієнта', 'started_on' => '2019-09-03', 'finished_on' => '2020-08-21']);
        $this->attachStacks($cabinet, 'Laravel', 'Vue 2');
        $landing = ProfileProject::factory()->createOne(['work_experience_id' => $experience->id, 'name' => 'Промо-лендинг', 'started_on' => '2020-03-01']);
        $auction = $this->project();

        Livewire::test(ManageWorkExperienceProjects::class, ['record' => $experience->id])
            ->assertCanSeeTableRecords([$landing, $cabinet], inOrder: true)
            ->assertCanNotSeeTableRecords([$auction])
            ->assertSee('09.2019 — 08.2020')
            ->assertSee('Vue 2')
            ->assertDontSee('Аукціон з продажу автомобілів');
    }

    /**
     * @return void
     */
    public function testStackKeepsSelectionOrderInFormAndTable(): void
    {
        $this->actingAs($this->user);
        $stack = $this->stackIds('Vuex', 'MySQL', 'Laravel', 'Vue 2');
        $selected = [$stack[2], $stack[3], $stack[0], $stack[1]];

        $this->projectsPage()
            ->callAction(TestAction::make('create'), data: [...$this->validData(), 'stacks' => $selected])
            ->assertHasNoFormErrors();

        $project = ProfileProject::query()->sole();

        $this->assertSame(['Laravel', 'Vue 2', 'Vuex', 'MySQL'], $project->stacks()->pluck('name')->all());
        $this->projectsPage()->assertSeeInOrder(['Laravel', 'Vue 2', 'Vuex', 'MySQL']);
        $this->projectsPage()
            ->mountAction(TestAction::make('edit')->table($project))
            ->assertSchemaStateSet(['stacks' => $selected]);
    }

    /**
     * @return void
     */
    public function testCreatesStackFromProjectFormKeepingFormOpen(): void
    {
        $this->actingAs($this->user);
        [$laravel] = $this->stackIds('Laravel');

        $component = $this->projectsPage()
            ->mountAction(TestAction::make('create'))
            ->fillForm(['stacks' => [$laravel]])
            ->callAction(TestAction::make('createOption')->schemaComponent('stacks'), data: ['name' => 'Vuex']);

        $vuex = Stack::query()->where('name', 'Vuex')->firstOrFail();

        $component
            ->assertSchemaStateSet(['stacks' => [$laravel, $vuex->id]])
            ->assertActionMounted(TestAction::make('create'));
        $this->assertDatabaseCount(ProfileProject::class, 0);
    }

    /**
     * @return void
     */
    public function testCreatesProjectWithoutStack(): void
    {
        $this->actingAs($this->user);

        $this->projectsPage()
            ->callAction(TestAction::make('create'), data: [...$this->validData(), 'stacks' => []])
            ->assertHasNoFormErrors();

        $this->assertCount(0, ProfileProject::query()->sole()->stacks()->get());
    }

    /**
     * @return void
     */
    public function testRemovingStackFromStackKeepsCatalog(): void
    {
        $this->actingAs($this->user);
        $project = $this->project();

        $this->projectsPage()
            ->callAction(TestAction::make('edit')->table($project), data: ['stacks' => $this->stackIds('PostgreSQL', 'jQuery')])
            ->assertHasNoFormErrors();

        $this->assertSame(['PostgreSQL', 'jQuery'], $project->stacks()->pluck('name')->all());
        $this->assertDatabaseHas(Stack::class, ['name' => 'MySQL']);
    }

    /**
     * @return void
     */
    public function testEmptyListOffersCreateAction(): void
    {
        $this->actingAs($this->user);

        $this->projectsPage()
            ->assertCountTableRecords(0)
            ->assertActionVisible(TestAction::make('create'));
    }

    /**
     * @return void
     */
    public function testEditsNameOfProject(): void
    {
        $this->actingAs($this->user);
        $project = $this->project(['name' => ['uk' => 'Аукціон'], 'description' => ['uk' => '<p>Опис</p>']]);

        $this->projectsPage()
            ->mountAction(TestAction::make('edit')->table($project))
            ->assertSchemaStateSet([
                'name.uk' => 'Аукціон',
                'started_on' => '2020-08-29',
                'finished_on' => '2020-10-12',
                'stacks' => $this->stackIds('PostgreSQL', 'MySQL', 'jQuery'),
            ])
            ->fillForm(['name' => ['uk' => 'Аукціон з продажу автомобілів']])
            ->callMountedAction()
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertSee('Аукціон з продажу автомобілів');

        $project->refresh();
        $this->assertSame('Аукціон з продажу автомобілів', $project->name);
        $this->assertStringContainsString('Опис', (string) $project->description);
        $this->assertSame($this->experience->id, $project->work_experience_id);
    }

    /**
     * @return void
     */
    public function testDeletesProjectAfterConfirmation(): void
    {
        $this->actingAs($this->user);
        $cabinet = $this->project(['name' => 'Особистий кабінет клієнта']);
        $landing = $this->project(['name' => 'Промо-лендинг']);

        $this->projectsPage()
            ->mountAction(TestAction::make('delete')->table($landing))
            ->assertActionMounted(TestAction::make('delete')->table($landing))
            ->callMountedAction()
            ->assertNotified()
            ->assertCanSeeTableRecords([$cabinet])
            ->assertCanNotSeeTableRecords([$landing]);

        $this->assertModelMissing($landing);
    }

    /**
     * @return void
     */
    public function testBulkDeletesSelectedProjects(): void
    {
        $this->actingAs($this->user);
        $first = $this->project();
        $second = $this->project();
        $kept = $this->project();

        $this->projectsPage()
            ->selectTableRecords([$first, $second])
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertNotified();

        $this->assertModelMissing($first);
        $this->assertModelMissing($second);
        $this->assertModelExists($kept);
    }

    /**
     * @return list<array{string}>
     */
    public static function recordActionProvider(): array
    {
        return [['edit'], ['delete']];
    }

    /**
     * @param string $action
     *
     * @return void
     */
    #[DataProvider('recordActionProvider')]
    public function testForeignProjectActionIsRejected(string $action): void
    {
        $this->actingAs($this->user);
        $foreign = ProfileProject::factory()->createOne(['name' => 'Аукціон']);

        try {
            $this->projectsPage()
                ->callAction(TestAction::make($action)->table($foreign), data: ['name' => ['uk' => 'Зламано']]);
            $this->fail('Action on a foreign project was not rejected.');
        } catch (ActionNotResolvableException) {
        }

        $this->assertModelExists($foreign);
        $this->assertSame('Аукціон', $foreign->refresh()->name);
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return ProfileProject
     */
    private function project(array $attributes = []): ProfileProject
    {
        $project = ProfileProject::factory()->createOne([
            'work_experience_id' => $this->experience->id,
            'name' => 'Аукціон з продажу автомобілів',
            'started_on' => '2020-08-29',
            'finished_on' => '2020-10-12',
            ...$attributes,
        ]);
        $this->attachStacks($project, 'PostgreSQL', 'MySQL', 'jQuery');

        return $project;
    }

    /**
     * @param ProfileProject $project
     * @param string ...$names
     *
     * @return void
     */
    private function attachStacks(ProfileProject $project, string ...$names): void
    {
        foreach ($this->stackIds(...$names) as $sort => $id) {
            $project->stacks()->attach($id, ['sort' => $sort]);
        }
    }

    /**
     * @return Testable<ManageWorkExperienceProjects>
     */
    private function projectsPage(): Testable
    {
        return Livewire::test(ManageWorkExperienceProjects::class, ['record' => $this->experience->id]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validData(): array
    {
        return [
            'name' => ['uk' => 'Аукціон з продажу автомобілів'],
            'started_on' => '2020-08-29',
            'finished_on' => '2020-10-12',
            'stacks' => $this->stackIds('PostgreSQL', 'MySQL', 'jQuery'),
        ];
    }

    /**
     * @param string ...$names
     *
     * @return list<int>
     */
    private function stackIds(string ...$names): array
    {
        return array_map(static fn (string $name): int => Stack::query()->firstOrCreate(['name' => $name])->id, array_values($names));
    }
}
