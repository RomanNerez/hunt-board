<?php

namespace App\Containers\AppSection\Vacancy\Tests\Functional\Filament\VacancySources;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tests\FunctionalTestCase;
use App\Containers\AppSection\VacancySource\Models\VacancySource;
use App\Containers\AppSection\VacancySource\UI\Filament\Resources\VacancySources\Pages\ManageVacancySources;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class DeletingVacancySourceTest extends FunctionalTestCase
{
    /**
     * @return void
     */
    public function testDeletingSourceKeepsVacancyWithRemainingSources(): void
    {
        $this->actingAs(User::factory()->createOne());

        $linkedIn = VacancySource::factory()->createOne(['name' => 'LinkedIn']);
        $dou = VacancySource::factory()->createOne(['name' => 'DOU']);
        $vacancy = Vacancy::factory()
            ->hasAttached($linkedIn, ['url' => 'https://www.linkedin.com/jobs/view/456/'])
            ->hasAttached($dou, ['url' => 'https://jobs.dou.ua/vacancies/123/'])
            ->createOne(['title' => 'PHP Developer']);

        Livewire::test(ManageVacancySources::class)
            ->callAction(TestAction::make('delete')->table($linkedIn))
            ->assertNotified();

        $this->assertModelMissing($linkedIn);
        $this->assertModelExists($vacancy);
        $this->assertSame(['DOU'], $vacancy->vacancySources()->pluck('name')->all());
        $this->assertDatabaseHas('vacancy_vacancy_source', ['vacancy_id' => $vacancy->id, 'vacancy_source_id' => $dou->id, 'url' => 'https://jobs.dou.ua/vacancies/123/']);
    }
}
