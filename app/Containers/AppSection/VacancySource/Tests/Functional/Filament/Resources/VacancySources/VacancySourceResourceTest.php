<?php

namespace App\Containers\AppSection\VacancySource\Tests\Functional\Filament\Resources\VacancySources;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\VacancySource\Tests\FunctionalTestCase;
use App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Pages\ManageVacancySources;
use App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\VacancySourceResource;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VacancySourceResource::class)]
final class VacancySourceResourceTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testPageIsServedUnderSettingsPrefixWithActiveSubNavigationItem(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->assertSame(url('/admin/settings/vacancy-sources'), VacancySourceResource::getUrl('index'));

        $this->get('/admin/settings/vacancy-sources')
            ->assertOk()
            ->assertSeeInOrder(['fi-page-sub-navigation-sidebar', 'aria-current="page"', 'Джерела вакансій'], false);
    }

    /**
     * @return void
     */
    public function testHasNoSeparateCreateOrEditPages(): void
    {
        $this->assertSame(['index'], array_keys(VacancySourceResource::getPages()));
    }

    /**
     * @return void
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get('/admin/settings/vacancy-sources')->assertRedirect(url('/admin/login'));
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
        $this->assertNotContains('Джерела вакансій', $labels);
    }

    /**
     * @return void
     */
    public function testSubNavigationListsVacancySourcesLast(): void
    {
        $this->actingAs(User::factory()->createOne());

        /** @var ManageVacancySources $page */
        $page = Livewire::test(ManageVacancySources::class)->instance();

        $this->assertSame(
            ['Статуси', 'Типи компаній', 'Домени', 'Технології', 'Джерела вакансій'],
            $this->navigationLabels($page->getCachedSubNavigation()),
        );
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
