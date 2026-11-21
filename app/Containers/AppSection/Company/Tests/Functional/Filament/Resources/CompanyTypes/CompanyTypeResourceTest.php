<?php

namespace App\Containers\AppSection\Company\Tests\Functional\Filament\Resources\CompanyTypes;

use App\Containers\AppSection\Company\Tests\FunctionalTestCase;
use App\Containers\AppSection\Company\UI\Filament\Resources\CompanyTypes\CompanyTypeResource;
use App\Containers\AppSection\Company\UI\Filament\Resources\CompanyTypes\Pages\ManageCompanyTypes;
use App\Containers\AppSection\User\Models\User;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CompanyTypeResource::class)]
final class CompanyTypeResourceTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testPageIsServedUnderSettingsPrefixWithActiveSubNavigationItem(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->assertSame(url('/admin/settings/company-types'), CompanyTypeResource::getUrl('index'));

        $this->get('/admin/settings/company-types')
            ->assertOk()
            ->assertSeeInOrder(['fi-page-sub-navigation-sidebar', 'aria-current="page"', 'Типи компаній'], false);
    }

    /**
     * @return void
     */
    public function testHasNoSeparateCreateOrEditPages(): void
    {
        $this->assertSame(['index'], array_keys(CompanyTypeResource::getPages()));
    }

    /**
     * @return void
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get('/admin/settings/company-types')->assertRedirect(url('/admin/login'));
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
        $this->assertNotContains('Типи компаній', $labels);
    }

    /**
     * @return void
     */
    public function testSubNavigationListsStatusesFirstThenCompanyTypesAndDomains(): void
    {
        $this->actingAs(User::factory()->createOne());

        /** @var ManageCompanyTypes $page */
        $page = Livewire::test(ManageCompanyTypes::class)->instance();

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
