<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeEducation;
use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Containers\AppSection\Resume\Models\ResumeLanguage;
use App\Containers\AppSection\Resume\Models\ResumeLink;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\EditResume;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\EditResumeLinks;
use App\Containers\AppSection\User\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Navigation\NavigationItem;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EditResume::class)]
final class EditResumeTest extends FunctionalTestCase
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
    public function testShowsCurrentValuesAndSavesChangesStayingOnPage(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id, 'headline' => 'Старий заголовок']);

        Livewire::test(EditResume::class, ['record' => $resume->id])
            ->assertSchemaStateSet(['headline' => 'Старий заголовок'])
            ->fillForm(['headline' => 'PHP Developer'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNoRedirect();

        $this->assertSame('PHP Developer', $resume->refresh()->headline);
        $this->assertSame($this->user->id, $resume->user_id);
    }

    /**
     * @return void
     */
    public function testFormHasOnlyGeneralFields(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);

        Livewire::test(EditResume::class, ['record' => $resume->id])
            ->assertFormFieldExists('first_name')
            ->assertFormFieldExists('last_name')
            ->assertFormFieldExists('headline')
            ->assertFormFieldExists('summary')
            ->assertFormFieldDoesNotExist('links')
            ->assertFormFieldDoesNotExist('experiences')
            ->assertFormFieldDoesNotExist('educations')
            ->assertFormFieldDoesNotExist('languages');
    }

    /**
     * @return void
     */
    public function testRejectsEmptyRequiredFields(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id, 'first_name' => 'Старе']);

        Livewire::test(EditResume::class, ['record' => $resume->id])
            ->fillForm(['first_name' => '', 'last_name' => ''])
            ->call('save')
            ->assertHasFormErrors(['first_name' => 'required', 'last_name' => 'required']);

        $this->assertSame('Старе', $resume->refresh()->first_name);
    }

    /**
     * @return void
     */
    public function testShowsSubNavigationInOrder(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);

        $items = Livewire::test(EditResume::class, ['record' => $resume->id])->instance()->getSubNavigation();

        $this->assertSame(
            ['Загальне', 'Посилання', 'Досвід', 'Освіта', 'Мови'],
            array_map(static fn (NavigationItem $item): string => $item->getLabel(), $items),
        );
    }

    /**
     * @return void
     */
    public function testSubNavigationHighlightsCurrentSection(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);

        $this->get("/admin/resumes/{$resume->id}/languages")
            ->assertOk()
            ->assertSeeInOrder(['Загальне', 'Посилання', 'Досвід', 'Освіта', 'Мови']);
    }

    /**
     * @return void
     */
    public function testSavingGeneralDoesNotTouchOtherSections(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'label' => 'GitHub']);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'MacPaw']);
        ResumeEducation::factory()->createOne(['resume_id' => $resume->id, 'institution' => 'KPI']);
        ResumeLanguage::factory()->createOne(['resume_id' => $resume->id, 'language' => 'Англійська']);

        Livewire::test(EditResume::class, ['record' => $resume->id])
            ->fillForm(['first_name' => 'Нове'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['GitHub'], $resume->links()->pluck('label')->all());
        $this->assertSame(['MacPaw'], $resume->experiences()->pluck('company')->all());
        $this->assertSame(['KPI'], $resume->educations()->pluck('institution')->all());
        $this->assertSame(['Англійська'], $resume->languages()->pluck('language')->all());
    }

    /**
     * @return void
     */
    public function testSavingOneSectionDoesNotTouchGeneralOrOtherSections(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id, 'first_name' => 'Старе']);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'MacPaw']);

        Livewire::test(EditResumeLinks::class, ['record' => $resume->id])
            ->fillForm(['links' => [['label' => 'GitHub', 'url' => 'https://github.com/john', 'is_visible' => true]]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Старе', $resume->refresh()->first_name);
        $this->assertSame(['MacPaw'], $resume->experiences()->pluck('company')->all());
    }

    /**
     * @return void
     */
    public function testDeleteFromPageRemovesResume(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);

        Livewire::test(EditResume::class, ['record' => $resume->id])
            ->callAction(TestAction::make('delete'))
            ->assertNotified();

        $this->assertModelMissing($resume);
    }
}
