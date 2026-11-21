<?php

namespace App\Containers\AppSection\Status\Tests\Functional\Filament\Resources\Statuses;

use App\Containers\AppSection\Status\Tests\FunctionalTestCase;
use App\Containers\AppSection\Status\UI\Filament\Resources\Statuses\Pages\ManageStatuses;
use App\Containers\AppSection\Status\UI\Filament\Resources\Statuses\StatusResource;
use App\Containers\AppSection\User\Models\User;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(StatusResource::class)]
final class StatusResourceTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testStatusesPageIsServedUnderSettingsPrefix(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->assertSame(url('/admin/settings/statuses'), StatusResource::getUrl('index'));

        $this->get('/admin/settings/statuses')
            ->assertOk()
            ->assertSeeInOrder(['fi-page-sub-navigation-sidebar', 'aria-current="page"', 'Statuses'], false);
    }

    /**
     * @return void
     */
    public function testHasNoSeparateCreateOrEditPages(): void
    {
        $this->assertSame(['index'], array_keys(StatusResource::getPages()));
    }

    /**
     * @return void
     */
    public function testSettingsEntryOpensStatusesPage(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->get('/admin/settings')->assertRedirect(url('/admin/settings/statuses'));
    }

    /**
     * @return void
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get('/admin/settings/statuses')->assertRedirect(url('/admin/login'));
    }

    /**
     * @return void
     */
    public function testMainNavigationShowsSettingsButNotStatuses(): void
    {
        $this->actingAs(User::factory()->createOne());
        Filament::setCurrentPanel('admin');
        Filament::bootCurrentPanel();

        $labels = $this->navigationLabels(Filament::getNavigation());

        $this->assertContains('Settings', $labels);
        $this->assertNotContains('Statuses', $labels);
    }

    /**
     * @return void
     */
    public function testSubNavigationIsOnTheLeftAndListsStatuses(): void
    {
        $this->actingAs(User::factory()->createOne());

        /** @var ManageStatuses $page */
        $page = Livewire::test(ManageStatuses::class)->instance();

        $labels = $this->navigationLabels($page->getCachedSubNavigation());

        $this->assertSame(SubNavigationPosition::Start, $page::getSubNavigationPosition());
        $this->assertSame(['Statuses'], $labels);
    }

    /**
     * @param array<NavigationGroup> $navigation
     *
     * @return list<string>
     */
    private function navigationLabels(array $navigation): array
    {
        $labels = [];

        foreach ($navigation as $group) {
            /** @var NavigationItem $item */
            foreach (collect($group->getItems()) as $item) {
                $label = $item->getLabel();
                $labels[] = $label instanceof Htmlable ? $label->toHtml() : $label;
            }
        }

        return $labels;
    }
}
