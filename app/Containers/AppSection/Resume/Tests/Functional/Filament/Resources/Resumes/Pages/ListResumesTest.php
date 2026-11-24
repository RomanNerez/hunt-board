<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\ListResumes;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\ResumeResource;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Tables\ResumesTable;
use App\Containers\AppSection\User\Models\User;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ListResumes::class)]
#[CoversClass(ResumesTable::class)]
final class ListResumesTest extends FunctionalTestCase
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
    public function testShowsOnlyOwnResumesWithNameKindAndHeadline(): void
    {
        $own = Resume::factory()->master()->createOne([
            'user_id' => $this->user->id,
            'first_name' => 'Роман',
            'last_name' => 'Нерез',
            'headline' => 'PHP Developer',
        ]);
        $foreign = Resume::factory()->createOne(['first_name' => 'Чужий', 'last_name' => 'Користувач']);

        Livewire::test(ListResumes::class)
            ->assertCanSeeTableRecords([$own])
            ->assertCanNotSeeTableRecords([$foreign])
            ->assertSee(['Роман Нерез', 'PHP Developer'])
            ->assertDontSee('Чужий Користувач');
    }

    /**
     * @return void
     */
    public function testRecentlyUpdatedResumeComesFirst(): void
    {
        $older = Resume::factory()->createOne(['user_id' => $this->user->id, 'updated_at' => '2026-11-01 10:00:00']);
        $newer = Resume::factory()->createOne(['user_id' => $this->user->id, 'updated_at' => '2026-11-20 10:00:00']);

        Livewire::test(ListResumes::class)->assertCanSeeTableRecords([$newer, $older], inOrder: true);
    }

    /**
     * @return void
     */
    public function testEmptyStateOffersToCreateResume(): void
    {
        Livewire::test(ListResumes::class)
            ->assertSee('Резюме ще немає')
            ->assertActionVisible(TestAction::make('create')->table());
    }

    /**
     * @return void
     */
    public function testCreateButtonLeadsToCreatePage(): void
    {
        Livewire::test(ListResumes::class)
            ->assertActionHasUrl('create', ResumeResource::getUrl('create'));
    }

    /**
     * @return void
     */
    public function testCardLinksAndEditActionLeadToEditPage(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        $editUrl = ResumeResource::getUrl('edit', ['record' => $resume]);

        Livewire::test(ListResumes::class)
            ->assertTableActionHasUrl('edit', $editUrl, $resume);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesResumeAfterConfirmation(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);

        Livewire::test(ListResumes::class)
            ->mountAction(TestAction::make('delete')->table($resume))
            ->callMountedAction()
            ->assertCanNotSeeTableRecords([$resume]);

        $this->assertModelMissing($resume);
    }

    /**
     * @return void
     */
    public function testCancellingDeleteKeepsResume(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);

        Livewire::test(ListResumes::class)
            ->mountAction(TestAction::make('delete')->table($resume))
            ->unmountAction()
            ->assertCanSeeTableRecords([$resume]);

        $this->assertModelExists($resume);
    }
}
