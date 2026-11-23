<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Clusters;

use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Clusters\Profile\ProfileCluster;
use App\Containers\AppSection\User\Models\User;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Contracts\Support\Htmlable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProfileCluster::class)]
final class ProfileClusterTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get('/admin/profile/general')->assertRedirect(url('/admin/login'));
    }

    /**
     * @return void
     */
    public function testGuestIsRedirectedToLoginFromSkills(): void
    {
        $this->get('/admin/profile/skills')->assertRedirect(url('/admin/login'));
    }

    /**
     * @return void
     */
    public function testGuestIsRedirectedToLoginFromWorkExperience(): void
    {
        $this->get('/admin/profile/work-experience')->assertRedirect(url('/admin/login'));
    }

    /**
     * @return void
     */
    public function testClusterEntryOpensGeneralPage(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->get('/admin/profile')->assertRedirect(url('/admin/profile/general'));
        $this->get('/admin/profile/general')->assertOk()->assertSee('Загальне');
    }

    /**
     * @return void
     */
    public function testMainNavigationShowsProfileButNotItsPages(): void
    {
        $this->actingAs(User::factory()->createOne());
        Filament::setCurrentPanel('admin');
        Filament::bootCurrentPanel();

        $labels = [];

        foreach (Filament::getNavigation() as $group) {
            /** @var NavigationItem $item */
            foreach (collect($group->getItems()) as $item) {
                $label = $item->getLabel();
                $labels[] = $label instanceof Htmlable ? $label->toHtml() : $label;
            }
        }

        $this->assertContains('Профіль', $labels);

        foreach (['Загальне', 'Досвід роботи', 'Навички', 'Посилання', 'Освіта', 'Мови'] as $page) {
            $this->assertNotContains($page, $labels);
        }
    }
}
