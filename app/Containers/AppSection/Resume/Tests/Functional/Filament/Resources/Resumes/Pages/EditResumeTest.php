<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Models\Resume;
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
}
