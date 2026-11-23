<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Pages;

use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileEducation;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileGeneral;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileLanguages;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileLinks;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileSkills;
use App\Containers\AppSection\Profile\UI\Filament\Pages\ProfileFormPage;
use App\Containers\AppSection\Profile\UI\Filament\Resources\WorkExperiences\Pages\ManageWorkExperiences;
use App\Containers\AppSection\User\Models\User;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProfileFormPage::class)]
#[CoversClass(ManageWorkExperiences::class)]
final class ProfileSubNavigationTest extends FunctionalTestCase
{
    /**
     * @var list<string>
     */
    private const ORDER = ['Загальне', 'Досвід роботи', 'Навички', 'Посилання', 'Освіта', 'Мови'];

    /**
     * @return void
     */
    public function testEducationPageShowsOrderedSubNavigationOnTheLeftWithEducationActive(): void
    {
        $this->actingAs(User::factory()->createOne());

        /** @var EditProfileEducation $page */
        $page = Livewire::test(EditProfileEducation::class)->instance();

        $this->assertSame(SubNavigationPosition::Start, $page::getSubNavigationPosition());
        $this->assertSame(self::ORDER, $this->subNavigationLabels($page));

        $this->get('/admin/profile/education')
            ->assertOk()
            ->assertSeeInOrder(['fi-page-sub-navigation-sidebar', 'Загальне', 'Досвід роботи', 'Навички', 'Посилання', 'aria-current="page"', 'Освіта', 'Мови'], false);
    }

    /**
     * @return void
     */
    public function testSkillsPageIsThirdAndActiveOnItsPage(): void
    {
        $this->actingAs(User::factory()->createOne());

        /** @var EditProfileSkills $page */
        $page = Livewire::test(EditProfileSkills::class)->instance();

        $this->assertSame(self::ORDER, $this->subNavigationLabels($page));

        $this->get('/admin/profile/skills')
            ->assertOk()
            ->assertSeeInOrder(['fi-page-sub-navigation-sidebar', 'Загальне', 'Досвід роботи', 'aria-current="page"', 'Навички', 'Посилання', 'Освіта', 'Мови'], false);
    }

    /**
     * @return void
     */
    public function testWorkExperiencePageIsSecondAndActiveOnItsPage(): void
    {
        $this->actingAs(User::factory()->createOne());

        /** @var ManageWorkExperiences $page */
        $page = Livewire::test(ManageWorkExperiences::class)->instance();

        $this->assertSame(SubNavigationPosition::Start, $page::getSubNavigationPosition());
        $this->assertSame(self::ORDER, $this->subNavigationLabels($page));

        $this->get('/admin/profile/work-experience')
            ->assertOk()
            ->assertSeeInOrder(['fi-page-sub-navigation-sidebar', 'Загальне', 'aria-current="page"', 'Досвід роботи', 'Навички', 'Посилання', 'Освіта', 'Мови'], false);
    }

    /**
     * @return void
     */
    public function testPagesAreServedUnderProfilePrefix(): void
    {
        $this->actingAs(User::factory()->createOne());

        $pages = [
            'general' => EditProfileGeneral::class,
            'work-experience' => ManageWorkExperiences::class,
            'skills' => EditProfileSkills::class,
            'links' => EditProfileLinks::class,
            'education' => EditProfileEducation::class,
            'languages' => EditProfileLanguages::class,
        ];

        foreach ($pages as $slug => $page) {
            $this->assertSame(url("/admin/profile/{$slug}"), $page::getUrl());
            $this->get("/admin/profile/{$slug}")->assertOk();
        }
    }

    /**
     * @param Page $page
     *
     * @return list<string>
     */
    private function subNavigationLabels(Page $page): array
    {
        $labels = [];

        foreach ($page->getCachedSubNavigation() as $group) {
            /** @var NavigationItem $item */
            foreach (collect($group->getItems()) as $item) {
                $label = $item->getLabel();
                $labels[] = $label instanceof Htmlable ? $label->toHtml() : $label;
            }
        }

        return $labels;
    }
}
