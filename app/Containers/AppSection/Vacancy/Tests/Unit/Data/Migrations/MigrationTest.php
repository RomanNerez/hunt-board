<?php

namespace App\Containers\AppSection\Vacancy\Tests\Unit\Data\Migrations;

use App\Containers\AppSection\Vacancy\Models\Vacancy;
use App\Containers\AppSection\Vacancy\Tests\UnitTestCase;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class MigrationTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testVacanciesTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('vacancies', [
            'id' => 'int8',
            'company_id' => 'int8',
            'status_id' => 'int8',
            'title' => 'varchar',
            'description' => 'text',
            'location' => 'varchar',
            'work_format' => 'varchar',
            'salary_min' => 'int4',
            'salary_max' => 'int4',
            'currency' => 'varchar',
            'applied_at' => 'date',
            'next_action_at' => 'timestamp',
            'notes' => 'text',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
    }

    /**
     * @return void
     */
    public function testDeletingCompanyDeletesItsVacancies(): void
    {
        $vacancy = Vacancy::factory()->createOne();
        $otherVacancy = Vacancy::factory()->createOne();

        $vacancy->company()->delete();

        $this->assertModelMissing($vacancy);
        $this->assertModelExists($otherVacancy);
    }

    /**
     * @return void
     */
    public function testDeletingStatusUsedByVacancyIsRejected(): void
    {
        $vacancy = Vacancy::factory()->createOne();

        try {
            DB::transaction(static fn (): mixed => $vacancy->status()->delete());
            $this->fail('Deleting a status used by a vacancy must fail.');
        } catch (QueryException $exception) {
            $this->assertSame('23503', $exception->getCode());
        }

        $this->assertDatabaseHas('statuses', ['id' => $vacancy->status_id]);
        $this->assertModelExists($vacancy);
    }
}
