<?php

namespace App\Containers\AppSection\Vacancy\Tests\Unit\Tasks;

use App\Containers\AppSection\Company\Models\Company;
use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tasks\CreateVacancyTask;
use App\Containers\AppSection\Vacancy\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateVacancyTask::class)]
final class CreateVacancyTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesVacancy(): void
    {
        $company = Company::factory()->createOne();
        $status = Status::factory()->createOne();

        $vacancy = app(CreateVacancyTask::class)->run([
            'company_id' => $company->id,
            'status_id' => $status->id,
            'title' => 'Senior PHP Developer',
        ]);

        $this->assertDatabaseHas(Vacancy::class, [
            'id' => $vacancy->id,
            'company_id' => $company->id,
            'status_id' => $status->id,
            'title' => 'Senior PHP Developer',
        ]);
    }
}
