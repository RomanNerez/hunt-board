<?php

namespace App\Containers\AppSection\Contact\Tests\Functional\Filament\Resources\Contacts;

use App\Containers\AppSection\Contact\Tests\FunctionalTestCase;
use App\Containers\AppSection\Contact\UI\Filament\Resources\Contacts\ContactResource;
use App\Containers\AppSection\User\Models\User;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Contracts\Support\Htmlable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ContactResource::class)]
final class ContactResourceTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get('/admin/contacts')->assertRedirect(url('/admin/login'));
    }

    /**
     * @return void
     */
    public function testHasNoSeparateCreateOrEditPages(): void
    {
        $this->assertSame(['index'], array_keys(ContactResource::getPages()));
    }

    /**
     * @return void
     */
    public function testPageIsServedAtContactsPath(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->assertSame(url('/admin/contacts'), ContactResource::getUrl('index'));
        $this->get('/admin/contacts')->assertOk()->assertSee('Контакти');
    }

    /**
     * @return void
     */
    public function testMainNavigationShowsContactsAfterCompanies(): void
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

        $companiesIndex = array_search('Компанії', $labels, true);

        $this->assertIsInt($companiesIndex);
        $this->assertSame('Контакти', $labels[$companiesIndex + 1] ?? null);
    }
}
