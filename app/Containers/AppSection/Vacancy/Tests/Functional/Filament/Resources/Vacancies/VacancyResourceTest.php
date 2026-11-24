<?php

namespace App\Containers\AppSection\Vacancy\Tests\Functional\Filament\Resources\Vacancies;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tests\FunctionalTestCase;
use App\Containers\AppSection\Vacancy\UI\Filament\Resources\Vacancies\VacancyResource;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Contracts\Support\Htmlable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VacancyResource::class)]
final class VacancyResourceTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testGuestIsRedirectedToLogin(): void
    {
        $this->get('/admin/vacancies')->assertRedirect(url('/admin/login'));
    }

    /**
     * @return void
     */
    public function testPageIsServedAtVacanciesPath(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->assertSame(url('/admin/vacancies'), VacancyResource::getUrl('index'));
        $this->get('/admin/vacancies')->assertOk()->assertSee('Вакансії');
    }

    /**
     * @return void
     */
    public function testHasOnlyIndexViewAndNotesPages(): void
    {
        $this->assertSame(['index', 'view', 'notes'], array_keys(VacancyResource::getPages()));
    }

    /**
     * @return void
     */
    public function testCreatePagePathIsNotFound(): void
    {
        $this->actingAs(User::factory()->createOne());

        $this->get('/admin/vacancies/create')->assertNotFound();
    }

    /**
     * @return void
     */
    public function testEditPagePathIsNotFound(): void
    {
        $this->actingAs(User::factory()->createOne());
        $vacancy = Vacancy::factory()->createOne();

        $this->get("/admin/vacancies/{$vacancy->id}/edit")->assertNotFound();
    }

    /**
     * @return void
     */
    public function testMainNavigationShowsVacanciesBeforeCompanies(): void
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

        $this->assertContains('Вакансії', $labels);
        $this->assertLessThan(array_search('Компанії', $labels, true), array_search('Вакансії', $labels, true));
    }
}
