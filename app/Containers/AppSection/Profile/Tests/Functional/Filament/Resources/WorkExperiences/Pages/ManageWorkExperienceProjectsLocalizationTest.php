<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Resources\WorkExperiences\Pages;

use App\Containers\AppSection\Profile\Enums\ProfileLocale;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Pages\ManageWorkExperienceProjects;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Schemas\ProjectForm;
use App\Containers\AppSection\User\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

use function Safe\json_encode;

#[CoversClass(ManageWorkExperienceProjects::class)]
#[CoversClass(ProjectForm::class)]
final class ManageWorkExperienceProjectsLocalizationTest extends FunctionalTestCase
{
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

        $user = User::factory()->createOne();
        $this->actingAs($user);
        $this->experience = ProfileWorkExperience::factory()->createOne([
            'user_id' => $user->id,
            'started_on' => '2020-08-29',
            'finished_on' => '2020-10-12',
        ]);
    }

    /**
     * @return void
     */
    public function testEditModalShowsEnglishValuesWhenEnglishIsActive(): void
    {
        $project = ProfileProject::factory()->createOne([
            'work_experience_id' => $this->experience->id,
            'name' => ['uk' => 'Аукціон з продажу автомобілів', 'en' => 'Car auction'],
            'description' => ['uk' => '<p>Міграція бази даних</p>', 'en' => '<p>Database migration</p>'],
        ]);

        $component = $this->projectsPage()
            ->set('dataLocale', 'en')
            ->mountAction(TestAction::make('edit')->table($project))
            ->assertFormFieldExists('name.en', static fn (TextInput $field): bool => "\$wire.dataLocale === 'en'" === $field->getVisibleJs() && !$field->isRequired())
            ->assertFormFieldExists('name.uk', static fn (TextInput $field): bool => "\$wire.dataLocale === 'uk'" === $field->getVisibleJs() && $field->isRequired())
            ->assertFormFieldExists('description.en', static fn (RichEditor $field): bool => "\$wire.dataLocale === 'en'" === $field->getVisibleJs())
            ->assertFormFieldExists('description.uk', static fn (RichEditor $field): bool => "\$wire.dataLocale === 'uk'" === $field->getVisibleJs())
            ->assertSchemaStateSet(['name.en' => 'Car auction']);

        $this->assertStringContainsString('Database migration', json_encode($component->get('mountedActions.0.data.description.en'), JSON_UNESCAPED_UNICODE));
    }

    /**
     * @return void
     */
    public function testBothDescriptionLanguagesAreSavedInOneActionAndPageLocaleFollowsModal(): void
    {
        $this->projectsPage()
            ->mountAction(TestAction::make('create'))
            ->fillForm([...$this->validData(), 'description' => ['uk' => '<p>Міграція бази даних</p>']])
            ->callAction(TestAction::make('dataLocale_en')->schemaComponent('dataLocale'))
            ->assertSet('dataLocale', 'en')
            ->assertActionMounted(TestAction::make('create'))
            ->assertSchemaStateSet(['name.uk' => 'Аукціон з продажу автомобілів'])
            ->fillForm(['description' => ['uk' => '<p>Міграція бази даних</p>', 'en' => '<p>Database migration</p>']])
            ->callMountedAction()
            ->assertHasNoFormErrors()
            ->assertSet('dataLocale', 'en');

        $translations = ProfileProject::query()->sole()->getTranslations('description');

        $this->assertStringContainsString('Міграція бази даних', $translations['uk'] ?? '');
        $this->assertStringContainsString('Database migration', $translations['en'] ?? '');
    }

    /**
     * @return void
     */
    public function testMissingUkrainianNameWhileEnglishIsActiveSwitchesToUkrainian(): void
    {
        $component = $this->projectsPage()
            ->set('dataLocale', 'en')
            ->callAction(TestAction::make('create'), data: [...$this->validData(), 'name' => ['uk' => '', 'en' => 'Car auction']])
            ->assertHasFormErrors(['name.uk' => 'required'])
            ->assertSet('dataLocale', 'uk');

        $this->assertContains('Поле назва проєкту є обов’язковим.', $component->errors()->all());
        $this->assertDatabaseCount(ProfileProject::class, 0);
    }

    /**
     * @return void
     */
    public function testDescriptionWithoutEnglishReadsUkrainianInEnglish(): void
    {
        $this->projectsPage()
            ->callAction(TestAction::make('create'), data: [...$this->validData(), 'description' => ['uk' => '<p>Запущено промо-лендинг</p>', 'en' => '']])
            ->assertHasNoFormErrors();

        $translations = ProfileProject::query()->sole()->getTranslations('description');

        $this->assertSame(['uk'], array_keys($translations));
        $this->assertStringContainsString('Запущено промо-лендинг', (string) ManageWorkExperienceProjects::translationWithFallback($translations, ProfileLocale::English));
    }

    /**
     * @return void
     */
    public function testEnglishTableKeepsSharedFieldsAndFallsBackToUkrainian(): void
    {
        ProfileProject::factory()->createOne([
            'work_experience_id' => $this->experience->id,
            'name' => ['uk' => 'Аукціон з продажу автомобілів'],
            'started_on' => '2020-08-29',
            'finished_on' => '2020-10-12',
            'technologies' => ['Laravel', 'Vue 2'],
        ]);
        ProfileProject::factory()->createOne([
            'work_experience_id' => $this->experience->id,
            'name' => ['uk' => 'Промо-лендинг', 'en' => 'Promo landing'],
            'started_on' => '2020-09-01',
        ]);

        $this->projectsPage()
            ->assertSee('Промо-лендинг')
            ->set('dataLocale', 'en')
            ->assertSee('Promo landing')
            ->assertDontSee('Промо-лендинг')
            ->assertSee('Аукціон з продажу автомобілів')
            ->assertSee('08.2020 — 10.2020')
            ->assertSeeInOrder(['Laravel', 'Vue 2']);
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
            'technologies' => ['PostgreSQL'],
        ];
    }
}
