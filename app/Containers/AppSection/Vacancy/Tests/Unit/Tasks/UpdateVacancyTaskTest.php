<?php

namespace App\Containers\AppSection\Vacancy\Tests\Unit\Tasks;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tasks\UpdateVacancyTask;
use App\Containers\AppSection\Vacancy\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateVacancyTask::class)]
final class UpdateVacancyTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUpdatesVacancyTitle(): void
    {
        $vacancy = Vacancy::factory()->createOne(['title' => 'PHP Developer']);

        $result = app(UpdateVacancyTask::class)->run($vacancy->id, ['title' => 'Senior PHP Developer']);

        $this->assertSame($vacancy->id, $result->id);
        $this->assertDatabaseHas(Vacancy::class, ['id' => $vacancy->id, 'title' => 'Senior PHP Developer']);
    }
}
