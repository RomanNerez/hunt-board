<?php

namespace App\Containers\AppSection\VacancySource\Tests\Functional\Filament\Resources\VacancySources\Pages;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\Tests\FunctionalTestCase;
use App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Pages\ManageVacancySources;
use App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Tables\VacancySourcesTable;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\MessageBag;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ManageVacancySources::class)]
#[CoversClass(VacancySourcesTable::class)]
final class ManageVacancySourcesTest extends FunctionalTestCase
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
        $linkedIn = VacancySource::factory()->createOne(['name' => 'LinkedIn']);
        $dou = VacancySource::factory()->createOne(['name' => 'DOU']);
        $djinni = VacancySource::factory()->createOne(['name' => 'Djinni']);

        Livewire::test(ManageVacancySources::class)
            ->assertCanSeeTableRecords([$djinni, $dou, $linkedIn], inOrder: true);
    }

    /**
     * @return void
     */
    public function testSearchFiltersByName(): void
    {
        $dou = VacancySource::factory()->createOne(['name' => 'DOU']);
        $linkedIn = VacancySource::factory()->createOne(['name' => 'LinkedIn']);

        Livewire::test(ManageVacancySources::class)
            ->searchTable('Link')
            ->assertCanSeeTableRecords([$linkedIn])
            ->assertCanNotSeeTableRecords([$dou]);
    }

    /**
     * @return void
     */
    public function testCreatesRecordInModal(): void
    {
        Livewire::test(ManageVacancySources::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'Djinni'])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(VacancySource::class, ['name' => 'Djinni']);
    }

    /**
     * @return void
     */
    public function testCreateRejectsEmptyNameWithUkrainianMessage(): void
    {
        $component = Livewire::test(ManageVacancySources::class)
            ->callAction(TestAction::make('create'), data: ['name' => ''])
            ->assertHasFormErrors(['name' => 'required']);

        /** @var MessageBag $errors */
        $errors = $component->errors();

        $this->assertContains('Поле назва є обов’язковим.', $errors->all());
        $this->assertDatabaseCount(VacancySource::class, 0);
    }

    /**
     * @return void
     */
    public function testCreateRejectsDuplicateNameWithUkrainianMessage(): void
    {
        VacancySource::factory()->createOne(['name' => 'DOU']);

        $component = Livewire::test(ManageVacancySources::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'DOU'])
            ->assertHasFormErrors(['name' => 'unique']);

        /** @var MessageBag $errors */
        $errors = $component->errors();

        $this->assertContains('Таке значення поля назва уже зайняте.', $errors->all());
        $this->assertDatabaseCount(VacancySource::class, 1);
    }

    /**
     * @return void
     */
    public function testEditsName(): void
    {
        $record = VacancySource::factory()->createOne(['name' => 'Linkedin']);

        Livewire::test(ManageVacancySources::class)
            ->callAction(TestAction::make('edit')->table($record), data: ['name' => 'LinkedIn'])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(VacancySource::class, ['id' => $record->id, 'name' => 'LinkedIn']);
    }

    /**
     * @return void
     */
    public function testEditKeepsOwnNameWithoutUniqueError(): void
    {
        $record = VacancySource::factory()->createOne(['name' => 'LinkedIn']);

        Livewire::test(ManageVacancySources::class)
            ->callAction(TestAction::make('edit')->table($record), data: ['name' => 'LinkedIn'])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(VacancySource::class, ['id' => $record->id, 'name' => 'LinkedIn']);
    }

    /**
     * @return void
     */
    public function testDeletesSingleRecord(): void
    {
        $record = VacancySource::factory()->createOne();
        $other = VacancySource::factory()->createOne();

        Livewire::test(ManageVacancySources::class)
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
        $dou = VacancySource::factory()->createOne(['name' => 'DOU']);
        $djinni = VacancySource::factory()->createOne(['name' => 'Djinni']);
        $kept = VacancySource::factory()->createOne(['name' => 'LinkedIn']);

        Livewire::test(ManageVacancySources::class)
            ->selectTableRecords([$dou, $djinni])
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertNotified();

        $this->assertModelMissing($dou);
        $this->assertModelMissing($djinni);
        $this->assertModelExists($kept);
    }
}
