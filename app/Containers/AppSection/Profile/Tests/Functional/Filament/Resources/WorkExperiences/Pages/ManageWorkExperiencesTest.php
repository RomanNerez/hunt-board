<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Resources\WorkExperiences\Pages;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Profile\Enums\WorkExperienceType;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Pages\ManageWorkExperiences;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Tables\WorkExperiencesTable;
use App\Containers\AppSection\User\Models\User;
use Filament\Actions\Exceptions\ActionNotResolvableException;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Select;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ManageWorkExperiences::class)]
#[CoversClass(WorkExperiencesTable::class)]
final class ManageWorkExperiencesTest extends FunctionalTestCase
{
    /**
     * @var User
     */
    private User $user;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->createOne();
        $this->actingAs($this->user);
    }

    /**
     * @return void
     */
    public function testCreatesFinishedCompanyExperience(): void
    {
        Livewire::test(ManageWorkExperiences::class)
            ->callAction(TestAction::make('create'), data: [
                'company_name' => ['uk' => 'MacPaw'],
                'type' => WorkExperienceType::COMPANY->value,
                'role' => ['uk' => 'Senior PHP-розробник'],
                'started_on' => '2021-03-01',
                'finished_on' => '2024-08-31',
            ])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $record = ProfileWorkExperience::query()->sole();

        $this->assertSame($this->user->id, $record->user_id);
        $this->assertSame('MacPaw', $record->company_name);
        $this->assertSame(WorkExperienceType::COMPANY, $record->type);
        $this->assertSame('Senior PHP-розробник', $record->role);
        $this->assertSame('2021-03-01', $record->started_on->toDateString());
        $this->assertSame('2024-08-31', $record->finished_on?->toDateString());
    }

    /**
     * @return void
     */
    public function testCurrentJobIsStoredWithoutFinishDateAndShownAsOngoing(): void
    {
        Livewire::test(ManageWorkExperiences::class)
            ->callAction(TestAction::make('create'), data: [
                'company_name' => ['uk' => 'Grammarly'],
                'type' => WorkExperienceType::COMPANY->value,
                'role' => ['uk' => 'Backend Engineer'],
                'started_on' => '2024-09-01',
            ])
            ->assertHasNoFormErrors()
            ->assertSee('09.2024 — дотепер');

        $this->assertNull(ProfileWorkExperience::query()->sole()->finished_on);
    }

    /**
     * @return void
     */
    public function testCreatesFreelanceProjectWithoutAddingCompany(): void
    {
        Livewire::test(ManageWorkExperiences::class)
            ->callAction(TestAction::make('create'), data: [
                'company_name' => ['uk' => 'Інтернет-магазин для Kavapro'],
                'type' => WorkExperienceType::FREELANCE->value,
                'role' => ['uk' => 'Full-stack розробник'],
                'started_on' => '2020-01-01',
                'finished_on' => '2020-06-30',
            ])
            ->assertHasNoFormErrors();

        $this->assertSame(WorkExperienceType::FREELANCE, ProfileWorkExperience::query()->sole()->type);
        $this->assertDatabaseCount(Company::class, 0);
    }

    /**
     * @return void
     */
    public function testTypeOffersCompanyAndFreelance(): void
    {
        Livewire::test(ManageWorkExperiences::class)
            ->mountAction(TestAction::make('create'))
            ->assertFormFieldExists('type', static fn (Select $field): bool => ['company' => 'Компанія', 'freelance' => 'Фриланс-проєкт'] === $field->getOptions());
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function invalidDataProvider(): array
    {
        return [
            'missing role' => [['role' => ['uk' => '']], 'role.uk', 'required'],
            'missing company' => [['company_name' => ['uk' => '']], 'company_name.uk', 'required'],
            'missing type' => [['type' => null], 'type', 'required'],
            'missing start date' => [['started_on' => null], 'started_on', 'required'],
            'too long company' => [['company_name' => ['uk' => str_repeat('a', 256)]], 'company_name.uk', 'max'],
            'too long english role' => [['role' => ['uk' => 'PHP-розробник', 'en' => str_repeat('a', 256)]], 'role.en', 'max'],
            'finished before started' => [['started_on' => '2020-09-01', 'finished_on' => '2019-06-30'], 'finished_on', 'after_or_equal'],
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
    public function testInvalidRecordIsNotCreated(array $override, string $field, string $rule): void
    {
        Livewire::test(ManageWorkExperiences::class)
            ->callAction(TestAction::make('create'), data: [...$this->validData(), ...$override])
            ->assertHasFormErrors([$field => $rule])
            ->assertNotNotified();

        $this->assertDatabaseCount(ProfileWorkExperience::class, 0);
    }

    /**
     * @return void
     */
    public function testValidationMessagesAreUkrainian(): void
    {
        $component = Livewire::test(ManageWorkExperiences::class)
            ->callAction(TestAction::make('create'), data: [...$this->validData(), 'role' => ['uk' => ''], 'started_on' => '2020-09-01', 'finished_on' => '2019-06-30']);

        $messages = $component->errors()->all();

        $this->assertContains('Поле роль є обов’язковим.', $messages);
        $this->assertContains('Поле дата завершення має містити дату не раніше дата початку.', $messages);
    }

    /**
     * @return void
     */
    public function testShowsRecordsFromNewestStart(): void
    {
        $older = ProfileWorkExperience::factory()->createOne(['user_id' => $this->user->id, 'started_on' => '2019-03-01', 'finished_on' => '2021-02-28']);
        $newer = ProfileWorkExperience::factory()->current()->createOne(['user_id' => $this->user->id, 'started_on' => '2024-09-01']);

        Livewire::test(ManageWorkExperiences::class)
            ->assertCanSeeTableRecords([$newer, $older], inOrder: true)
            ->assertSee('03.2019 — 02.2021');
    }

    /**
     * @return void
     */
    public function testEmptyListOffersCreateAction(): void
    {
        Livewire::test(ManageWorkExperiences::class)
            ->assertCountTableRecords(0)
            ->assertActionVisible(TestAction::make('create'));
    }

    /**
     * @return void
     */
    public function testEditsRoleOfRecord(): void
    {
        $record = ProfileWorkExperience::factory()->createOne([
            'user_id' => $this->user->id,
            'company_name' => 'MacPaw',
            'role' => ['uk' => 'PHP-розробник', 'en' => 'PHP Developer'],
        ]);

        Livewire::test(ManageWorkExperiences::class)
            ->mountAction(TestAction::make('edit')->table($record))
            ->assertSchemaStateSet([
                'company_name.uk' => 'MacPaw',
                'role.uk' => 'PHP-розробник',
                'role.en' => 'PHP Developer',
                'started_on' => $record->started_on->toDateString(),
            ])
            ->fillForm(['role' => ['uk' => 'Senior PHP-розробник', 'en' => 'PHP Developer']])
            ->callMountedAction()
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertSee('Senior PHP-розробник');

        $this->assertEquals(['uk' => 'Senior PHP-розробник', 'en' => 'PHP Developer'], $record->refresh()->getTranslations('role'));
        $this->assertSame($this->user->id, $record->user_id);
    }

    /**
     * @return void
     */
    public function testDeletesRecordAfterConfirmation(): void
    {
        $macPaw = ProfileWorkExperience::factory()->createOne(['user_id' => $this->user->id, 'company_name' => 'MacPaw']);
        $grammarly = ProfileWorkExperience::factory()->createOne(['user_id' => $this->user->id, 'company_name' => 'Grammarly']);

        Livewire::test(ManageWorkExperiences::class)
            ->mountAction(TestAction::make('delete')->table($macPaw))
            ->assertActionMounted(TestAction::make('delete')->table($macPaw))
            ->callMountedAction()
            ->assertNotified()
            ->assertCanSeeTableRecords([$grammarly])
            ->assertCanNotSeeTableRecords([$macPaw]);

        $this->assertModelMissing($macPaw);
    }

    /**
     * @return void
     */
    public function testBulkDeletesSelectedRecords(): void
    {
        $first = ProfileWorkExperience::factory()->createOne(['user_id' => $this->user->id]);
        $second = ProfileWorkExperience::factory()->createOne(['user_id' => $this->user->id]);
        $kept = ProfileWorkExperience::factory()->createOne(['user_id' => $this->user->id]);

        Livewire::test(ManageWorkExperiences::class)
            ->selectTableRecords([$first, $second])
            ->callAction(TestAction::make('delete')->table()->bulk())
            ->assertNotified();

        $this->assertModelMissing($first);
        $this->assertModelMissing($second);
        $this->assertModelExists($kept);
    }

    /**
     * @return void
     */
    public function testForeignRecordsAreHidden(): void
    {
        $foreign = ProfileWorkExperience::factory()->createOne(['company_name' => 'MacPaw']);
        $own = ProfileWorkExperience::factory()->createOne(['user_id' => $this->user->id, 'company_name' => 'Grammarly']);

        Livewire::test(ManageWorkExperiences::class)
            ->assertCanSeeTableRecords([$own])
            ->assertCanNotSeeTableRecords([$foreign])
            ->assertDontSee('MacPaw');
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
    public function testForeignRecordActionIsRejected(string $action): void
    {
        $foreign = ProfileWorkExperience::factory()->createOne(['company_name' => 'MacPaw', 'role' => 'PHP-розробник']);

        try {
            Livewire::test(ManageWorkExperiences::class)
                ->callAction(TestAction::make($action)->table($foreign), data: ['role' => ['uk' => 'Зламано']]);
            $this->fail('Action on a foreign record was not rejected.');
        } catch (ActionNotResolvableException) {
        }

        $this->assertModelExists($foreign);
        $this->assertSame('PHP-розробник', $foreign->refresh()->role);
    }

    /**
     * @return array<string, mixed>
     */
    private function validData(): array
    {
        return [
            'company_name' => ['uk' => 'MacPaw'],
            'type' => WorkExperienceType::COMPANY->value,
            'role' => ['uk' => 'PHP-розробник'],
            'started_on' => '2021-03-01',
            'finished_on' => '2024-08-31',
        ];
    }
}
