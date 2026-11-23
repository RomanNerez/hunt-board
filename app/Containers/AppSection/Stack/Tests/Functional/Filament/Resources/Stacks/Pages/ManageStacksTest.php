<?php

namespace App\Containers\AppSection\Stack\Tests\Functional\Filament\Resources\Stacks\Pages;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\Tests\FunctionalTestCase;
use App\Containers\AppSection\Stack\UI\Filament\Resources\Stacks\Pages\ManageStacks;
use App\Containers\AppSection\User\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ManageStacks::class)]
final class ManageStacksTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->createOne());
    }

    /**
     * @return void
     */
    public function testListsRecordsSortedByName(): void
    {
        $vue = Stack::factory()->createOne(['name' => 'Vue.js']);
        $docker = Stack::factory()->createOne(['name' => 'Docker']);
        $laravel = Stack::factory()->createOne(['name' => 'Laravel']);

        Livewire::test(ManageStacks::class)
            ->assertCanSeeTableRecords([$docker, $laravel, $vue], inOrder: true);
    }

    /**
     * @return void
     */
    public function testSearchFiltersByName(): void
    {
        $laravel = Stack::factory()->createOne(['name' => 'Laravel']);
        $livewire = Stack::factory()->createOne(['name' => 'Livewire']);

        Livewire::test(ManageStacks::class)
            ->searchTable('Live')
            ->assertCanSeeTableRecords([$livewire])
            ->assertCanNotSeeTableRecords([$laravel]);
    }

    /**
     * @return void
     */
    public function testCreatesRecordInModalWithTrimmedName(): void
    {
        Livewire::test(ManageStacks::class)
            ->callAction(TestAction::make('create'), data: ['name' => ' Docker '])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Stack::class, ['name' => 'Docker']);
    }

    /**
     * @return void
     */
    public function testCreateRejectsEmptyName(): void
    {
        Livewire::test(ManageStacks::class)
            ->callAction(TestAction::make('create'), data: ['name' => ''])
            ->assertHasFormErrors(['name' => 'required']);

        $this->assertDatabaseCount(Stack::class, 0);
    }

    /**
     * @return void
     */
    public function testCreateRejectsTooLongName(): void
    {
        Livewire::test(ManageStacks::class)
            ->callAction(TestAction::make('create'), data: ['name' => str_repeat('a', 101)])
            ->assertHasFormErrors(['name' => 'max']);

        $this->assertDatabaseCount(Stack::class, 0);
    }

    /**
     * @return void
     */
    public function testCreateRejectsNameDifferingOnlyByCase(): void
    {
        Stack::factory()->createOne(['name' => 'PostgreSQL']);

        Livewire::test(ManageStacks::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'postgresql'])
            ->assertHasFormErrors(['name' => 'Таке значення поля назва уже зайняте.']);

        $this->assertDatabaseCount(Stack::class, 1);
    }

    /**
     * @return void
     */
    public function testEditsName(): void
    {
        $record = Stack::factory()->createOne(['name' => 'VueJS']);

        Livewire::test(ManageStacks::class)
            ->callAction(TestAction::make('edit')->table($record), data: ['name' => 'Vue.js'])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Stack::class, ['id' => $record->id, 'name' => 'Vue.js']);
    }

    /**
     * @return void
     */
    public function testEditAllowsChangingCaseOfOwnName(): void
    {
        $record = Stack::factory()->createOne(['name' => 'mysql']);

        Livewire::test(ManageStacks::class)
            ->callAction(TestAction::make('edit')->table($record), data: ['name' => 'MySQL'])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Stack::class, ['id' => $record->id, 'name' => 'MySQL']);
    }

    /**
     * @return void
     */
    public function testEditRejectsNameOfAnotherStack(): void
    {
        Stack::factory()->createOne(['name' => 'PostgreSQL']);
        $record = Stack::factory()->createOne(['name' => 'MySQL']);

        Livewire::test(ManageStacks::class)
            ->callAction(TestAction::make('edit')->table($record), data: ['name' => 'POSTGRESQL'])
            ->assertHasFormErrors(['name']);

        $this->assertDatabaseHas(Stack::class, ['id' => $record->id, 'name' => 'MySQL']);
    }

    /**
     * @return void
     */
    public function testRenamedStackIsShownInProject(): void
    {
        $project = ProfileProject::factory()->withStacks(2)->createOne();
        [$first, $second] = $project->stacks()->get()->all();

        Livewire::test(ManageStacks::class)
            ->callAction(TestAction::make('edit')->table($first), data: ['name' => 'Vue.js'])
            ->assertHasNoFormErrors();

        $this->assertSame(['Vue.js', $second->name], $project->stacks()->pluck('name')->all());
    }

    /**
     * @return void
     */
    public function testDeletingUsedStackKeepsProjectAndOrderOfRest(): void
    {
        $project = ProfileProject::factory()->withStacks(3)->createOne();
        [$postgres, $mysql, $jquery] = $project->stacks()->get()->all();

        Livewire::test(ManageStacks::class)
            ->callAction(TestAction::make('delete')->table($mysql))
            ->assertNotified();

        $this->assertModelMissing($mysql);
        $this->assertModelExists($project);
        $this->assertSame([$postgres->id, $jquery->id], $project->stacks()->pluck('stacks.id')->all());
    }

    /**
     * @return void
     */
    public function testBulkDeletesSelectedRecords(): void
    {
        $first = Stack::factory()->createOne();
        $second = Stack::factory()->createOne();
        $kept = Stack::factory()->createOne();

        Livewire::test(ManageStacks::class)
            ->selectTableRecords([$first, $second])
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertNotified();

        $this->assertModelMissing($first);
        $this->assertModelMissing($second);
        $this->assertModelExists($kept);
    }
}
