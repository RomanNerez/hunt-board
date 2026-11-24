<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeLink;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\EditResume;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
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
}
