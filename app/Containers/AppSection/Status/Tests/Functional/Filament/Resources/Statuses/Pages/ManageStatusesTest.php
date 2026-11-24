<?php

namespace App\Containers\AppSection\Status\Tests\Functional\Filament\Resources\Statuses\Pages;

use App\Containers\AppSection\Status\Enums\StatusColor;
use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\Tests\FunctionalTestCase;
use App\Containers\AppSection\Status\UI\Filament\Resources\Statuses\Pages\ManageStatuses;
use App\Containers\AppSection\Status\UI\Filament\Resources\Statuses\Tables\StatusesTable;
use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Livewire\Notifications;
use Filament\Notifications\Notification;
use Filament\Notifications\NotificationMailbox;
use Filament\Schemas\Components\Html;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ManageStatuses::class)]
#[CoversClass(StatusesTable::class)]
final class ManageStatusesTest extends FunctionalTestCase
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
    public function testShowsStatusesAsBadgesInPresetColors(): void
    {
        $applied = Status::factory()->createOne(['name' => 'Applied', 'color' => StatusColor::GRAY]);
        $offer = Status::factory()->createOne(['name' => 'Offer', 'color' => StatusColor::GREEN]);

        Livewire::test(ManageStatuses::class)
            ->assertCanSeeTableRecords([$applied, $offer], inOrder: true)
            ->assertSeeHtml('--hb-status-bg: #F9FAFB;')
            ->assertSeeHtml('--hb-status-bg: #F0FDF4;')
            ->assertSeeHtml('>Offer</span>');
    }

    /**
     * @return void
     */
    public function testSearchFiltersStatusesByName(): void
    {
        $applied = Status::factory()->createOne(['name' => 'Applied']);
        $offer = Status::factory()->createOne(['name' => 'Offer']);

        Livewire::test(ManageStatuses::class)
            ->searchTable('Off')
            ->assertCanSeeTableRecords([$offer])
            ->assertCanNotSeeTableRecords([$applied]);
    }

    /**
     * @return void
     */
    public function testDeletesSingleStatus(): void
    {
        $rejected = Status::factory()->createOne(['name' => 'Rejected']);
        $offer = Status::factory()->createOne(['name' => 'Offer']);

        Livewire::test(ManageStatuses::class)
            ->callAction(TestAction::make('delete')->table($rejected))
            ->assertNotified();

        $this->assertModelMissing($rejected);
        $this->assertModelExists($offer);
    }

    /**
     * @return void
     */
    public function testBulkDeletesSelectedStatuses(): void
    {
        $applied = Status::factory()->createOne(['name' => 'Applied']);
        $rejected = Status::factory()->createOne(['name' => 'Rejected']);
        $offer = Status::factory()->createOne(['name' => 'Offer']);

        Livewire::test(ManageStatuses::class)
            ->selectTableRecords([$applied, $rejected])
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertNotified();

        $this->assertModelMissing($applied);
        $this->assertModelMissing($rejected);
        $this->assertModelExists($offer);
    }

    /**
     * @return void
     */
    public function testDeletingStatusUsedByVacancyKeepsItAndNotifies(): void
    {
        $applied = Status::factory()->createOne(['name' => 'Applied']);
        Vacancy::factory()->createOne(['title' => 'PHP Developer', 'status_id' => $applied->id]);

        Livewire::test(ManageStatuses::class)
            ->callAction(TestAction::make('delete')->table($applied));

        $notifications = $this->sentNotifications();

        $this->assertCount(1, $notifications);
        $this->assertSame('Статус використовується вакансіями', $notifications[0]->getTitle());
        $this->assertSame('danger', $notifications[0]->getStatus());
        $this->assertModelExists($applied);
    }

    /**
     * @return void
     */
    public function testBulkDeleteKeepsStatusesUsedByVacanciesAndWarns(): void
    {
        $applied = Status::factory()->createOne(['name' => 'Applied']);
        $rejected = Status::factory()->createOne(['name' => 'Rejected']);
        Vacancy::factory()->createOne(['title' => 'PHP Developer', 'status_id' => $applied->id]);

        Livewire::test(ManageStatuses::class)
            ->selectTableRecords([$applied, $rejected])
            ->callAction(TestAction::make('delete')->table()->bulk());

        $notifications = $this->sentNotifications();

        $this->assertCount(1, $notifications);
        $this->assertSame('warning', $notifications[0]->getStatus());
        $this->assertStringContainsString('Частину статусів не видалено, бо їх використовують вакансії.', (string) $notifications[0]->getBody());
        $this->assertModelMissing($rejected);
        $this->assertModelExists($applied);
    }

    /**
     * @return list<Notification>
     */
    private function sentNotifications(): array
    {
        app(NotificationMailbox::class)->flush();

        $component = new Notifications();
        $component->mount();
        $component->pullNotifications();

        /** @var list<Notification> $notifications */
        $notifications = $component->notifications->values()->all();

        return $notifications;
    }

    /**
     * @return void
     */
    public function testCreateFormOffersAllPresetsAsBadges(): void
    {
        Livewire::test(ManageStatuses::class)
            ->mountAction(TestAction::make('create'))
            ->assertFormFieldExists('color', function (ToggleButtons $field): bool {
                $options = $field->getOptions();

                $this->assertSame(array_column(StatusColor::cases(), 'value'), array_keys($options));
                $this->assertEquals(StatusColor::PINK->renderBadge('Рожевий'), $options['pink']);

                return true;
            });
    }

    /**
     * @return void
     */
    public function testCreateFormPreviewsBadgeWithEnteredName(): void
    {
        Livewire::test(ManageStatuses::class)
            ->mountAction(TestAction::make('create'))
            ->fillForm(['name' => 'Offer', 'color' => 'green'])
            ->assertSchemaComponentExists('preview', checkComponentUsing: function (Html $preview): bool {
                $html = $preview->toEmbeddedHtml();

                $this->assertStringContainsString('>Offer</span>', $html);
                $this->assertStringContainsString('--hb-status-bg: #F0FDF4;', $html);

                return true;
            });
    }

    /**
     * @return void
     */
    public function testCreatesStatusInModal(): void
    {
        Livewire::test(ManageStatuses::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'Interview', 'color' => 'blue'])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Status::class, ['name' => 'Interview', 'color' => 'blue']);
    }

    /**
     * @return void
     */
    public function testCreateRejectsEmptyNameAndColor(): void
    {
        Livewire::test(ManageStatuses::class)
            ->callAction(TestAction::make('create'), data: ['name' => '', 'color' => null])
            ->assertHasFormErrors(['name' => 'required', 'color' => 'required'])
            ->assertNotNotified();

        $this->assertDatabaseCount(Status::class, 0);
    }

    /**
     * @return list<array{string}>
     */
    public static function colorOutsidePaletteProvider(): array
    {
        return [
            ['#3B82F6'],
            ['magenta'],
        ];
    }

    /**
     * @param string $color
     *
     * @return void
     */
    #[DataProvider('colorOutsidePaletteProvider')]
    public function testCreateRejectsColorOutsidePalette(string $color): void
    {
        Livewire::test(ManageStatuses::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'Interview', 'color' => $color])
            ->assertHasFormErrors(['color'])
            ->assertHasNoFormErrors(['name']);

        $this->assertDatabaseCount(Status::class, 0);
    }

    /**
     * @return void
     */
    public function testCreateRejectsNameLongerThan255Characters(): void
    {
        Livewire::test(ManageStatuses::class)
            ->callAction(TestAction::make('create'), data: ['name' => str_repeat('a', 256), 'color' => 'blue'])
            ->assertHasFormErrors(['name' => 'max']);

        $this->assertDatabaseCount(Status::class, 0);
    }

    /**
     * @return void
     */
    public function testCreateRejectsDuplicateName(): void
    {
        Status::factory()->createOne(['name' => 'Offer']);

        Livewire::test(ManageStatuses::class)
            ->callAction(TestAction::make('create'), data: ['name' => 'Offer', 'color' => 'green'])
            ->assertHasFormErrors(['name' => 'unique']);

        $this->assertDatabaseCount(Status::class, 1);
    }

    /**
     * @return void
     */
    public function testEditChangesPresetWithoutUniqueNameError(): void
    {
        $status = Status::factory()->createOne(['name' => 'Interview', 'color' => StatusColor::BLUE]);

        Livewire::test(ManageStatuses::class)
            ->callAction(TestAction::make('edit')->table($status), data: ['color' => 'amber'])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Status::class, ['id' => $status->id, 'name' => 'Interview', 'color' => 'amber']);
    }

    /**
     * @return void
     */
    public function testEditRejectsNameTakenByAnotherStatus(): void
    {
        Status::factory()->createOne(['name' => 'Offer']);
        $status = Status::factory()->createOne(['name' => 'Interview']);

        Livewire::test(ManageStatuses::class)
            ->callAction(TestAction::make('edit')->table($status), data: ['name' => 'Offer'])
            ->assertHasFormErrors(['name' => 'unique']);

        $this->assertDatabaseHas(Status::class, ['id' => $status->id, 'name' => 'Interview']);
    }

    /**
     * @return void
     */
    public function testEditRejectsColorOutsidePalette(): void
    {
        $status = Status::factory()->createOne(['color' => StatusColor::BLUE]);

        Livewire::test(ManageStatuses::class)
            ->callAction(TestAction::make('edit')->table($status), data: ['color' => 'magenta'])
            ->assertHasFormErrors(['color']);

        $this->assertDatabaseHas(Status::class, ['id' => $status->id, 'color' => 'blue']);
    }
}
