<?php

namespace App\Containers\AppSection\Company\Tests\Functional\Filament\Resources\Companies;

use App\Containers\AppSection\Company\Tests\FunctionalTestCase;
use App\Containers\AppSection\Company\UI\Filament\Resources\Companies\CompanyResource;
use App\Containers\AppSection\User\Models\User;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Contracts\Support\Htmlable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CompanyResource::class)]
final class CompanyResourceTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get('/admin/companies')->assertRedirect(url('/admin/login'));
    }

    /**
     * @return void
     */
    public function testHasNoSeparateCreateOrEditPages(): void
    {
        $this->assertSame(['index'], array_keys(CompanyResource::getPages()));
    }

    /**
     * @return void
     */
    public function testPageIsServedAtCompaniesPath(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->assertSame(url('/admin/companies'), CompanyResource::getUrl('index'));
        $this->get('/admin/companies')->assertOk()->assertSee('Компанії');
    }

    /**
     * @return void
     */
    public function testMainNavigationShowsCompanies(): void
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

        $this->assertContains('Компанії', $labels);
    }
}
