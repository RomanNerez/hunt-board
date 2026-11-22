<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Pages;

use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileEducation;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileGeneral;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileLanguages;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileLinks;
use App\Containers\AppSection\Profile\UI\Filament\Pages\ProfileFormPage;
use App\Containers\AppSection\User\Models\User;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProfileFormPage::class)]
final class ProfileSubNavigationTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testEducationPageShowsOrderedSubNavigationOnTheLeftWithEducationActive(): void
    {
        $this->actingAs(User::factory()->createOne());

        /** @var EditProfileEducation $page */
        $page = Livewire::test(EditProfileEducation::class)->instance();

        $labels = [];

        foreach ($page->getCachedSubNavigation() as $group) {
            /** @var NavigationItem $item */
            foreach (collect($group->getItems()) as $item) {
                $label = $item->getLabel();
                $labels[] = $label instanceof Htmlable ? $label->toHtml() : $label;
            }
        }

        $this->assertSame(SubNavigationPosition::Start, $page::getSubNavigationPosition());
        $this->assertSame(['Загальне', 'Посилання', 'Освіта', 'Мови'], $labels);

        $this->get('/admin/profile/education')
            ->assertOk()
            ->assertSeeInOrder(['fi-page-sub-navigation-sidebar', 'Загальне', 'Посилання', 'aria-current="page"', 'Освіта', 'Мови'], false);
    }

    /**
     * @return void
     */
    public function testPagesAreServedUnderProfilePrefix(): void
    {
        $this->actingAs(User::factory()->createOne());

        $pages = [
            'general' => EditProfileGeneral::class,
            'links' => EditProfileLinks::class,
            'education' => EditProfileEducation::class,
            'languages' => EditProfileLanguages::class,
        ];

        foreach ($pages as $slug => $page) {
            $this->assertSame(url("/admin/profile/{$slug}"), $page::getUrl());
            $this->get("/admin/profile/{$slug}")->assertOk();
        }
    }
}
