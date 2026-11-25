<?php

namespace App\Containers\AppSection\Resume\Tests\Functional\Filament\Resources\Resumes\Pages;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Tests\FunctionalTestCase;
use App\Containers\AppSection\Resume\UI\Filament\Resources\Resumes\Pages\PreviewResume;
use App\Containers\AppSection\User\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PreviewResume::class)]
final class PreviewResumeTest extends FunctionalTestCase
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
    public function testShowsFrameWithResumePdfAndPdfActionsWithoutSave(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);
        $previewUrl = route('resumes.pdf.preview', ['resume' => $resume->id]);

        Livewire::test(PreviewResume::class, ['record' => $resume->id])
            ->assertSeeHtml('src="' . $previewUrl . '"')
            ->assertActionHasUrl('openPdf', $previewUrl)
            ->assertActionShouldOpenUrlInNewTab('openPdf')
            ->assertActionHasUrl('downloadPdf', route('resumes.pdf.download', ['resume' => $resume->id]))
            ->assertActionDoesNotExist('save')
            ->assertSee(['Завантажити PDF', 'Відкрити в новій вкладці']);
    }

    /**
     * @return void
     */
    public function testPageIsServedAsLastTabWithoutSaveButton(): void
    {
        $resume = Resume::factory()->createOne(['user_id' => $this->user->id]);

        $this->get("/admin/resumes/{$resume->id}/preview")
            ->assertOk()
            ->assertSeeInOrder(['Загальне', 'Посилання', 'Досвід', 'Освіта', 'Мови', 'Перегляд'])
            ->assertDontSee('Зберегти');
    }

    /**
     * @return void
     */
    public function testForeignResumeIsNotFound(): void
    {
        $foreign = Resume::factory()->createOne();

        $this->get("/admin/resumes/{$foreign->id}/preview")->assertNotFound();
    }
}
