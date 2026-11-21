<?php

namespace App\Containers\AppSection\Company\Tests\Functional\Filament\Resources\CompanyTypes\Pages;

use App\Containers\AppSection\Company\Models\CompanyType;
use App\Containers\AppSection\Company\Tests\FunctionalTestCase;
use App\Containers\AppSection\Company\UI\Filament\Resources\CompanyTypes\Pages\ManageCompanyTypes;
use App\Containers\AppSection\User\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ManageCompanyTypes::class)]
final class ManageCompanyTypesTest extends FunctionalTestCase
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
        $first = CompanyType::factory()->createOne(['name' => 'Продуктова']);
        $second = CompanyType::factory()->createOne(['name' => 'Аутсорс']);

        $expected = strcmp('Продуктова', 'Аутсорс') < 0 ? [$first, $second] : [$second, $first];

        Livewire::test(ManageCompanyTypes::class)
            ->assertCanSeeTableRecords($expected, inOrder: true);
    }

    /**
     * @return void
     */
    public function testSearchFiltersByName(): void
    {
        $first = CompanyType::factory()->createOne(['name' => 'Продуктова']);
        $second = CompanyType::factory()->createOne(['name' => 'Аутсорс']);

        Livewire::test(ManageCompanyTypes::class)
            ->searchTable('Аут')
            ->assertCanSeeTableRecords([$second])
            ->assertCanNotSeeTableRecords([$first]);
    }

    /**
     * @return void
     */
    public function testCreatesRecordInModal(): void
    {
        Livewire::test(ManageCompanyTypes::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'Аутстаф'])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(CompanyType::class, ['name' => 'Аутстаф']);
    }

    /**
     * @return void
     */
    public function testCreateRejectsEmptyName(): void
    {
        Livewire::test(ManageCompanyTypes::class)
            ->callAction(TestAction::make('create'), data: ['name' => ''])
            ->assertHasFormErrors(['name' => 'required']);

        $this->assertDatabaseCount(CompanyType::class, 0);
    }

    /**
     * @return void
     */
    public function testCreateRejectsDuplicateName(): void
    {
        CompanyType::factory()->createOne(['name' => 'Продуктова']);

        Livewire::test(ManageCompanyTypes::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'Продуктова'])
            ->assertHasFormErrors(['name' => 'unique']);

        $this->assertDatabaseCount(CompanyType::class, 1);
    }

    /**
     * @return void
     */
    public function testEditsName(): void
    {
        $record = CompanyType::factory()->createOne(['name' => 'Продуктова']);

        Livewire::test(ManageCompanyTypes::class)
            ->callAction(TestAction::make('edit')->table($record), data: ['name' => 'Аутстаф'])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(CompanyType::class, ['id' => $record->id, 'name' => 'Аутстаф']);
    }

    /**
     * @return void
     */
    public function testEditKeepsOwnNameWithoutUniqueError(): void
    {
        $record = CompanyType::factory()->createOne(['name' => 'Продуктова']);

        Livewire::test(ManageCompanyTypes::class)
            ->callAction(TestAction::make('edit')->table($record), data: ['name' => 'Продуктова'])
            ->assertHasNoFormErrors();
    }

    /**
     * @return void
     */
    public function testEditRejectsEmptyName(): void
    {
        $record = CompanyType::factory()->createOne(['name' => 'Продуктова']);

        Livewire::test(ManageCompanyTypes::class)
            ->callAction(TestAction::make('edit')->table($record), data: ['name' => ''])
            ->assertHasFormErrors(['name' => 'required']);

        $this->assertDatabaseHas(CompanyType::class, ['id' => $record->id, 'name' => 'Продуктова']);
    }

    /**
     * @return void
     */
    public function testDeletesSingleRecord(): void
    {
        $record = CompanyType::factory()->createOne();
        $other = CompanyType::factory()->createOne();

        Livewire::test(ManageCompanyTypes::class)
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
        $first = CompanyType::factory()->createOne();
        $second = CompanyType::factory()->createOne();
        $kept = CompanyType::factory()->createOne();

        Livewire::test(ManageCompanyTypes::class)
            ->selectTableRecords([$first, $second])
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertNotified();

        $this->assertModelMissing($first);
        $this->assertModelMissing($second);
        $this->assertModelExists($kept);
    }
}
