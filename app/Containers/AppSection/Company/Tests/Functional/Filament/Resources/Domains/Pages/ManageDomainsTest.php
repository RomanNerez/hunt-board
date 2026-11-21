<?php

namespace App\Containers\AppSection\Company\Tests\Functional\Filament\Resources\Domains\Pages;

use App\Containers\AppSection\Company\Models\Domain;
use App\Containers\AppSection\Company\Tests\FunctionalTestCase;
use App\Containers\AppSection\Company\UI\Filament\Resources\Domains\Pages\ManageDomains;
use App\Containers\AppSection\User\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ManageDomains::class)]
final class ManageDomainsTest extends FunctionalTestCase
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
        $first = Domain::factory()->createOne(['name' => 'Fintech']);
        $second = Domain::factory()->createOne(['name' => 'Gamedev']);

        $expected = strcmp('Fintech', 'Gamedev') < 0 ? [$first, $second] : [$second, $first];

        Livewire::test(ManageDomains::class)
            ->assertCanSeeTableRecords($expected, inOrder: true);
    }

    /**
     * @return void
     */
    public function testSearchFiltersByName(): void
    {
        $first = Domain::factory()->createOne(['name' => 'Fintech']);
        $second = Domain::factory()->createOne(['name' => 'Gamedev']);

        Livewire::test(ManageDomains::class)
            ->searchTable('Game')
            ->assertCanSeeTableRecords([$second])
            ->assertCanNotSeeTableRecords([$first]);
    }

    /**
     * @return void
     */
    public function testCreatesRecordInModal(): void
    {
        Livewire::test(ManageDomains::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'EdTech'])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Domain::class, ['name' => 'EdTech']);
    }

    /**
     * @return void
     */
    public function testCreateRejectsEmptyName(): void
    {
        Livewire::test(ManageDomains::class)
            ->callAction(TestAction::make('create'), data: ['name' => ''])
            ->assertHasFormErrors(['name' => 'required']);

        $this->assertDatabaseCount(Domain::class, 0);
    }

    /**
     * @return void
     */
    public function testCreateRejectsDuplicateName(): void
    {
        Domain::factory()->createOne(['name' => 'Fintech']);

        Livewire::test(ManageDomains::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'Fintech'])
            ->assertHasFormErrors(['name' => 'unique']);

        $this->assertDatabaseCount(Domain::class, 1);
    }

    /**
     * @return void
     */
    public function testEditsName(): void
    {
        $record = Domain::factory()->createOne(['name' => 'Fintech']);

        Livewire::test(ManageDomains::class)
            ->callAction(TestAction::make('edit')->table($record), data: ['name' => 'EdTech'])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Domain::class, ['id' => $record->id, 'name' => 'EdTech']);
    }

    /**
     * @return void
     */
    public function testEditKeepsOwnNameWithoutUniqueError(): void
    {
        $record = Domain::factory()->createOne(['name' => 'Fintech']);

        Livewire::test(ManageDomains::class)
            ->callAction(TestAction::make('edit')->table($record), data: ['name' => 'Fintech'])
            ->assertHasNoFormErrors();
    }

    /**
     * @return void
     */
    public function testEditRejectsEmptyName(): void
    {
        $record = Domain::factory()->createOne(['name' => 'Fintech']);

        Livewire::test(ManageDomains::class)
            ->callAction(TestAction::make('edit')->table($record), data: ['name' => ''])
            ->assertHasFormErrors(['name' => 'required']);

        $this->assertDatabaseHas(Domain::class, ['id' => $record->id, 'name' => 'Fintech']);
    }

    /**
     * @return void
     */
    public function testDeletesSingleRecord(): void
    {
        $record = Domain::factory()->createOne();
        $other = Domain::factory()->createOne();

        Livewire::test(ManageDomains::class)
            ->callAction(TestAction::make('delete')->table($record))
            ->assertNotified();

        $this->assertModelMissing($record);
        $this->assertModelExists($other);
    }

    /**
     * @return void
     */
    public function testBulkDeletesSelectedRecords(): void
    {
        $first = Domain::factory()->createOne();
        $second = Domain::factory()->createOne();
        $kept = Domain::factory()->createOne();

        Livewire::test(ManageDomains::class)
            ->selectTableRecords([$first, $second])
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertNotified();

        $this->assertModelMissing($first);
        $this->assertModelMissing($second);
        $this->assertModelExists($kept);
    }
}
