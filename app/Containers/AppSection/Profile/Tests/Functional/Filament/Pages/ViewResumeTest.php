<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Pages;

use App\Containers\AppSection\Profile\Models\UserProfile;
use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileGeneral;
use App\Containers\AppSection\Profile\UI\Filament\Pages\ViewResume;
use App\Containers\AppSection\User\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ViewResume::class)]
final class ViewResumeTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testShowsUkrainianPreviewAndResumeActionsByDefault(): void
    {
        $this->actingAsUserWithProfile();

        Livewire::test(ViewResume::class)
            ->assertSet('dataLocale', 'uk')
            ->assertSeeHtml('src="' . route('profile.resume.preview', ['locale' => 'uk']) . '"')
            ->assertActionHasUrl('openResume', route('profile.resume.preview', ['locale' => 'uk']))
            ->assertActionShouldOpenUrlInNewTab('openResume')
            ->assertActionHasUrl('downloadResume', route('profile.resume.download', ['locale' => 'uk']))
            ->assertSee('Відкрити в новій вкладці')
            ->assertSee('Завантажити PDF');
    }

    /**
     * @return void
     */
    public function testSwitchingToEnglishPointsPreviewAndActionsToEnglishPdf(): void
    {
        $this->actingAsUserWithProfile();

        Livewire::test(ViewResume::class)
            ->callAction('dataLocale_en')
            ->assertSet('dataLocale', 'en')
            ->assertSeeHtml('src="' . route('profile.resume.preview', ['locale' => 'en']) . '"')
            ->assertDontSeeHtml(route('profile.resume.preview', ['locale' => 'uk']))
            ->assertActionHasUrl('openResume', route('profile.resume.preview', ['locale' => 'en']))
            ->assertActionHasUrl('downloadResume', route('profile.resume.download', ['locale' => 'en']));
    }

    /**
     * @return void
     */
    public function testWithoutProfileShowsLinkToGeneralPageInsteadOfPreview(): void
    {
        $this->actingAs(User::factory()->createOne());

        Livewire::test(ViewResume::class)
            ->assertSee('Щоб сформувати резюме, заповніть сторінку')
            ->assertSeeHtml('href="' . EditProfileGeneral::getUrl() . '"')
            ->assertDontSeeHtml('<iframe')
            ->assertActionHidden('openResume')
            ->assertActionHidden('downloadResume');
    }

    /**
     * @return void
     */
    public function testPageHasNoTemplateChoice(): void
    {
        $this->actingAsUserWithProfile();

        $this->get('/admin/profile/resume')
            ->assertOk()
            ->assertDontSee('Шаблон')
            ->assertDontSee('Класичний');
    }

    /**
     * @return void
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get('/admin/profile/resume')->assertRedirect('/admin/login');
    }

    /**
     * @return void
     */
    private function actingAsUserWithProfile(): void
    {
        $profile = UserProfile::factory()->createOne();

        $this->actingAs(User::query()->findOrFail($profile->user_id));
    }
}
