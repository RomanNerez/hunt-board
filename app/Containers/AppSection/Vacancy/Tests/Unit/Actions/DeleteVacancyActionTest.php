<?php

namespace App\Containers\AppSection\Vacancy\Tests\Unit\Actions;

use App\Containers\AppSection\Vacancy\Actions\DeleteVacancyAction;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteVacancyAction::class)]
final class DeleteVacancyActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesVacancy(): void
    {
        $vacancy = Vacancy::factory()->createOne();
        $otherVacancy = Vacancy::factory()->createOne();

        $result = app(DeleteVacancyAction::class)->run($vacancy->id);

        $this->assertTrue($result);
        $this->assertModelMissing($vacancy);
        $this->assertModelExists($otherVacancy);
    }
}
