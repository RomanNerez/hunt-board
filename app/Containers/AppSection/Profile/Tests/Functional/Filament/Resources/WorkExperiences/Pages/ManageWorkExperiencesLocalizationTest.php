<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Resources\WorkExperiences\Pages;

use App\Containers\AppSection\Profile\Enums\WorkExperienceType;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Concerns\InteractsWithDataLocale;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Pages\ManageWorkExperiences;
use App\Containers\AppSection\User\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\TextInput;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;

#[CoversClass(ManageWorkExperiences::class)]
#[CoversTrait(InteractsWithDataLocale::class)]
final class ManageWorkExperiencesLocalizationTest extends FunctionalTestCase
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
    public function testPageSwitchesDataLocale(): void
    {
        Livewire::test(ManageWorkExperiences::class)
            ->assertSet('dataLocale', 'uk')
            ->callAction('dataLocale_en')
            ->assertSet('dataLocale', 'en')
            ->callAction('dataLocale_uk')
            ->assertSet('dataLocale', 'uk');
    }

    /**
     * @return void
     */
    public function testModalShowsFieldsOfActiveLocale(): void
    {
        Livewire::test(ManageWorkExperiences::class)
            ->set('dataLocale', 'en')
            ->mountAction(TestAction::make('create'))
            ->assertFormFieldExists('company_name.uk', static fn (TextInput $field): bool => "\$wire.dataLocale === 'uk'" === $field->getVisibleJs())
            ->assertFormFieldExists('company_name.en', static fn (TextInput $field): bool => "\$wire.dataLocale === 'en'" === $field->getVisibleJs())
            ->assertFormFieldExists('role.uk', static fn (TextInput $field): bool => "\$wire.dataLocale === 'uk'" === $field->getVisibleJs() && $field->isRequired())
            ->assertFormFieldExists('role.en', static fn (TextInput $field): bool => "\$wire.dataLocale === 'en'" === $field->getVisibleJs() && !$field->isRequired());
    }

    /**
     * @return void
     */
    public function testRoleInBothLanguagesFollowsTableLocale(): void
    {
        Livewire::test(ManageWorkExperiences::class)
            ->callAction(TestAction::make('create'), data: [
                ...$this->validData(),
                'role' => ['uk' => 'Senior PHP-розробник', 'en' => 'Senior PHP Developer'],
            ])
            ->assertHasNoFormErrors()
            ->assertSee('Senior PHP-розробник')
            ->assertDontSee('Senior PHP Developer')
            ->set('dataLocale', 'en')
            ->assertSee('Senior PHP Developer')
            ->assertDontSee('Senior PHP-розробник');

        $this->assertEquals(
            ['uk' => 'Senior PHP-розробник', 'en' => 'Senior PHP Developer'],
            ProfileWorkExperience::query()->sole()->getTranslations('role'),
        );
    }

    /**
     * @return void
     */
    public function testEnglishTableFallsBackToUkrainianAndKeepsSharedFields(): void
    {
        ProfileWorkExperience::factory()->current()->createOne([
            'user_id' => $this->user->id,
            'type' => WorkExperienceType::FREELANCE,
            'company_name' => ['uk' => 'Інтернет-магазин для Kavapro'],
            'role' => ['uk' => 'PHP-розробник'],
            'started_on' => '2021-03-01',
        ]);

        Livewire::test(ManageWorkExperiences::class)
            ->set('dataLocale', 'en')
            ->assertSee('Інтернет-магазин для Kavapro')
            ->assertSee('PHP-розробник')
            ->assertSee('Фриланс-проєкт')
            ->assertSee('03.2021 — дотепер');
    }

    /**
     * @return void
     */
    public function testSwitchingLocaleInsideModalKeepsItOpenAndSavesBothLanguages(): void
    {
        Livewire::test(ManageWorkExperiences::class)
            ->mountAction(TestAction::make('create'))
            ->fillForm([...$this->validData(), 'role' => ['uk' => 'Senior PHP-розробник']])
            ->callAction(TestAction::make('dataLocale_en')->schemaComponent('dataLocale'))
            ->assertSet('dataLocale', 'en')
            ->assertActionMounted(TestAction::make('create'))
            ->assertSchemaStateSet(['role.uk' => 'Senior PHP-розробник', 'company_name.uk' => 'MacPaw'])
            ->fillForm(['role' => ['uk' => 'Senior PHP-розробник', 'en' => 'Senior PHP Developer']])
            ->callMountedAction()
            ->assertHasNoFormErrors()
            ->assertSet('dataLocale', 'en');

        $this->assertEquals(
            ['uk' => 'Senior PHP-розробник', 'en' => 'Senior PHP Developer'],
            ProfileWorkExperience::query()->sole()->getTranslations('role'),
        );
    }

    /**
     * @return void
     */
    public function testEmptyEnglishRoleIsSaved(): void
    {
        Livewire::test(ManageWorkExperiences::class)
            ->callAction(TestAction::make('create'), data: [...$this->validData(), 'role' => ['uk' => 'PHP-розробник', 'en' => '']])
            ->assertHasNoFormErrors();

        $this->assertSame(['uk' => 'PHP-розробник'], ProfileWorkExperience::query()->sole()->getTranslations('role'));
    }

    /**
     * @return void
     */
    public function testMissingUkrainianRoleWhileEnglishIsActiveSwitchesToUkrainian(): void
    {
        $component = Livewire::test(ManageWorkExperiences::class)
            ->set('dataLocale', 'en')
            ->callAction(TestAction::make('create'), data: [...$this->validData(), 'role' => ['uk' => '', 'en' => 'PHP Developer']])
            ->assertHasFormErrors(['role.uk' => 'required'])
            ->assertSet('dataLocale', 'uk');

        $this->assertContains('Поле роль є обов’язковим.', $component->errors()->all());
        $this->assertDatabaseCount(ProfileWorkExperience::class, 0);
    }

    /**
     * @return void
     */
    public function testEditModalFillsBothLanguages(): void
    {
        $record = ProfileWorkExperience::factory()->createOne([
            'user_id' => $this->user->id,
            'company_name' => ['uk' => 'Інтернет-магазин для Kavapro', 'en' => 'Online store for Kavapro'],
            'role' => ['uk' => 'Full-stack розробник'],
        ]);

        Livewire::test(ManageWorkExperiences::class)
            ->set('dataLocale', 'en')
            ->mountAction(TestAction::make('edit')->table($record))
            ->assertSchemaStateSet([
                'company_name.uk' => 'Інтернет-магазин для Kavapro',
                'company_name.en' => 'Online store for Kavapro',
                'role.uk' => 'Full-stack розробник',
                'role.en' => null,
            ]);
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
        ];
    }
}
