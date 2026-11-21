<?php

namespace App\Containers\AppSection\Status\Tests\Functional\Filament\Resources\Statuses\Pages;

use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\Tests\FunctionalTestCase;
use App\Containers\AppSection\Status\UI\Filament\Resources\Statuses\Pages\ManageStatuses;
use App\Containers\AppSection\User\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Html;
use Illuminate\Support\MessageBag;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ManageStatuses::class)]
final class ManageStatusesLocalizationTest extends FunctionalTestCase
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
    public function testApplicationLocaleIsUkrainian(): void
    {
        $this->assertSame('uk', app()->getLocale());
    }

    /**
     * @return void
     */
    public function testEmptyNameShowsUkrainianValidationMessage(): void
    {
        $component = Livewire::test(ManageStatuses::class)
            ->callAction(TestAction::make('create'), data: ['name' => '', 'color' => 'blue'])
            ->assertHasFormErrors(['name' => 'required']);

        /** @var MessageBag $errors */
        $errors = $component->errors();

        $this->assertContains('Поле назва є обов’язковим.', $errors->all());
        $this->assertDatabaseCount(Status::class, 0);
    }

    /**
     * @return void
     */
    public function testDuplicateNameShowsUkrainianValidationMessage(): void
    {
        Status::factory()->createOne(['name' => 'Оффер']);

        $component = Livewire::test(ManageStatuses::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'Оффер', 'color' => 'green'])
            ->assertHasFormErrors(['name' => 'unique']);

        /** @var MessageBag $errors */
        $errors = $component->errors();

        $this->assertContains('Таке значення поля назва уже зайняте.', $errors->all());
    }

    /**
     * @return void
     */
    public function testPageAndCreateFormUseUkrainianLabels(): void
    {
        $component = Livewire::test(ManageStatuses::class)
            ->assertSee('Статуси')
            ->mountAction(TestAction::make('create'))
            ->assertFormFieldExists('name', static fn (TextInput $field): bool => 'Назва' === $field->getLabel())
            ->assertFormFieldExists('color', static fn (ToggleButtons $field): bool => 'Колір' === $field->getLabel());

        /** @var ManageStatuses $page */
        $page = $component->instance();

        $this->assertSame('Створити статус', $page->getMountedAction()?->getModalHeading());
    }

    /**
     * @return void
     */
    public function testPreviewWithoutNameShowsSampleText(): void
    {
        Livewire::test(ManageStatuses::class)
            ->mountAction(TestAction::make('create'))
            ->fillForm(['color' => 'green'])
            ->assertSchemaComponentExists('preview', checkComponentUsing: function (Html $preview): bool {
                $this->assertStringContainsString('>Приклад</span>', $preview->toEmbeddedHtml());

                return true;
            });
    }
}
