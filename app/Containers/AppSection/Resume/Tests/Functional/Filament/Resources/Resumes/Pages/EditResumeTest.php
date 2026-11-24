<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Containers\AppSection\Resume\Models\ResumeLink;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\EditResume;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\User\Models\User;
use Filament\Actions\Testing\TestAction;
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
    public function testShowsCurrentValuesAndSavesChanges(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id, 'headline' => 'Старий заголовок']);

        Livewire::test(EditResume::class, ['record' => $resume->id])
            ->assertSchemaStateSet(['headline' => 'Старий заголовок'])
            ->fillForm(['headline' => 'PHP Developer'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertRedirect(ResumeResource::getUrl('index'));

        $this->assertSame('PHP Developer', $resume->refresh()->headline);
        $this->assertSame($this->user->id, $resume->user_id);
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

    /**
     * @return void
     */
    public function testFillsExistingLinksInSavedOrder(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'label' => 'GitHub', 'icon' => 'fab-github', 'sort_order' => 1]);
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'label' => 'LinkedIn', 'sort_order' => 0, 'is_visible' => false]);

        $state = Livewire::test(EditResume::class, ['record' => $resume->id])->get('data.links');

        $rows = array_values($state);

        $this->assertSame(['LinkedIn', 'GitHub'], array_column($rows, 'label'));
        $this->assertSame('fab-github', $rows[1]['icon']);
        $this->assertFalse($rows[0]['is_visible']);
    }

    /**
     * @return void
     */
    public function testSavesReorderedAndChangedLinks(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'label' => 'LinkedIn', 'sort_order' => 0]);
        ResumeLink::factory()->createOne(['resume_id' => $resume->id, 'label' => 'GitHub', 'sort_order' => 1]);

        Livewire::test(EditResume::class, ['record' => $resume->id])
            ->fillForm(['links' => [
                ['icon' => null, 'label' => 'GitHub', 'url' => 'https://github.com/john', 'is_visible' => true],
                ['icon' => null, 'label' => 'Telegram', 'url' => 'https://t.me/john', 'is_visible' => true],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['GitHub', 'Telegram'], $resume->links()->pluck('label')->all());
    }

    /**
     * @return void
     */
    public function testFillsExistingExperiencesInSavedOrder(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'MacPaw', 'employment_type' => 'full_time', 'sort_order' => 1]);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'Grammarly', 'sort_order' => 0, 'is_visible' => false]);

        $rows = array_values(Livewire::test(EditResume::class, ['record' => $resume->id])->get('data.experiences'));

        $this->assertSame(['Grammarly', 'MacPaw'], array_column($rows, 'company'));
        $this->assertSame('full_time', $rows[1]['employment_type']);
        $this->assertStringStartsWith('2021-03-01', $rows[1]['start_date']);
        $this->assertFalse($rows[0]['is_visible']);
    }

    /**
     * @return void
     */
    public function testSavesReorderedAndChangedExperiences(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'MacPaw', 'sort_order' => 0]);
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'Grammarly', 'sort_order' => 1]);

        Livewire::test(EditResume::class, ['record' => $resume->id])
            ->fillForm(['experiences' => [
                ['company' => 'Grammarly', 'position' => 'Dev', 'start_date' => '2019-01-01', 'is_visible' => true],
                ['company' => 'Preply', 'position' => 'Dev', 'start_date' => '2022-05-15', 'end_date' => '2023-01-10', 'is_visible' => false],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        $experiences = $resume->experiences()->get();

        $this->assertSame(['Grammarly', 'Preply'], $experiences->pluck('company')->all());
        $this->assertSame('2022-05-01', $experiences[1]->start_date->toDateString());
        $this->assertFalse($experiences[1]->is_visible);
    }

    /**
     * @return void
     */
    public function testFillsAndSavesExperienceStacks(): void
    {
        $php = Stack::factory()->createOne();
        $vue = Stack::factory()->createOne();
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        $experience = ResumeExperience::factory()->createOne(['resume_id' => $resume->id]);
        $experience->stacks()->attach([$vue->id => ['sort' => 0], $php->id => ['sort' => 1]]);

        $rows = array_values(Livewire::test(EditResume::class, ['record' => $resume->id])->get('data.experiences'));

        $this->assertSame([$vue->id, $php->id], array_map(intval(...), $rows[0]['stacks']));

        $rows[0]['stacks'] = [$php->id];

        Livewire::test(EditResume::class, ['record' => $resume->id])
            ->fillForm(['experiences' => $rows])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([$php->id], $resume->experiences()->sole()->stacks->modelKeys());
    }
}
