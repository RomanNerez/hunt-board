<?php

namespace App\Containers\AppSection\Vacancy\Tests\Unit\Tasks;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tasks\DeleteVacancyTask;
use App\Containers\AppSection\Vacancy\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteVacancyTask::class)]
final class DeleteVacancyTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesVacancy(): void
    {
        $vacancy = Vacancy::factory()->createOne();

        $result = app(DeleteVacancyTask::class)->run($vacancy->id);

        $this->assertTrue($result);
        $this->assertModelMissing($vacancy);
    }
}
