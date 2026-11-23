<?php

namespace App\Containers\AppSection\Stack\Tests\Functional\Filament\Resources\Stacks;

use App\Containers\AppSection\Stack\Tests\FunctionalTestCase;
use App\Containers\AppSection\Stack\UI\Filament\Resources\Stacks\Pages\ManageStacks;
use App\Containers\AppSection\Stack\UI\Filament\Resources\Stacks\StackResource;
use App\Containers\AppSection\User\Models\User;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(StackResource::class)]
final class StackResourceTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testPageIsServedUnderSettingsPrefixWithActiveSubNavigationItem(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->assertSame(url('/admin/settings/stacks'), StackResource::getUrl('index'));

        $this->get('/admin/settings/stacks')
            ->assertOk()
            ->assertSeeInOrder(['fi-page-sub-navigation-sidebar', 'aria-current="page"', 'Технології'], false);
    }

    /**
     * @return void
     */
    public function testHasNoSeparateCreateOrEditPages(): void
    {
        $this->assertSame(['index'], array_keys(StackResource::getPages()));
    }

    /**
     * @return void
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get('/admin/settings/stacks')->assertRedirect(url('/admin/login'));
    }

    /**
     * @return void
     */
    public function testMainNavigationDoesNotShowPage(): void
    {
        $this->actingAs(User::factory()->createOne());
        Filament::setCurrentPanel('admin');
        Filament::bootCurrentPanel();

        $labels = $this->navigationLabels(Filament::getNavigation());

        $this->assertContains('Налаштування', $labels);
        $this->assertNotContains('Технології', $labels);
    }

    /**
     * @return void
     */
    public function testSubNavigationListsStatusesFirstThenCompanyTypesDomainsAndStacks(): void
    {
        $this->actingAs(User::factory()->createOne());

        /** @var ManageStacks $page */
        $page = Livewire::test(ManageStacks::class)->instance();

        $this->assertSame(['Статуси', 'Типи компаній', 'Домени', 'Технології'], $this->navigationLabels($page->getCachedSubNavigation()));
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
