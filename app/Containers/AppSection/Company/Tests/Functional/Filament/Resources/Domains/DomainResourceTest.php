<?php

namespace App\Containers\AppSection\Company\Tests\Functional\Filament\Resources\Domains;

use App\Containers\AppSection\Company\Tests\FunctionalTestCase;
use App\Containers\AppSection\Company\UI\Filament\Resources\Domains\DomainResource;
use App\Containers\AppSection\Company\UI\Filament\Resources\Domains\Pages\ManageDomains;
use App\Containers\AppSection\User\Models\User;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DomainResource::class)]
final class DomainResourceTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testPageIsServedUnderSettingsPrefixWithActiveSubNavigationItem(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->assertSame(url('/admin/settings/domains'), DomainResource::getUrl('index'));

        $this->get('/admin/settings/domains')
            ->assertOk()
            ->assertSeeInOrder(['fi-page-sub-navigation-sidebar', 'aria-current="page"', 'Домени'], false);
    }

    /**
     * @return void
     */
    public function testHasNoSeparateCreateOrEditPages(): void
    {
        $this->assertSame(['index'], array_keys(DomainResource::getPages()));
    }

    /**
     * @return void
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get('/admin/settings/domains')->assertRedirect(url('/admin/login'));
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
        $this->assertNotContains('Домени', $labels);
    }

    /**
     * @return void
     */
    public function testSubNavigationListsStatusesFirstThenCompanyTypesAndDomains(): void
    {
        $this->actingAs(User::factory()->createOne());

        /** @var ManageDomains $page */
        $page = Livewire::test(ManageDomains::class)->instance();

        $this->assertSame(['Статуси', 'Типи компаній', 'Домени'], $this->navigationLabels($page->getCachedSubNavigation()));
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
