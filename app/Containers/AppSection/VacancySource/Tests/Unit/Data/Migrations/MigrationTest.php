<?php

namespace App\Containers\AppSection\VacancySource\Tests\Unit\Data\Migrations;

use App\Containers\AppSection\VacancySource\Tests\UnitTestCase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class MigrationTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testVacancySourcesTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('vacancy_sources', [
            'id' => 'int8',
            'name' => 'varchar',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
    }

    /**
     * @return void
     */
    public function testVacancySourceNameIsUnique(): void
    {
        $uniqueColumns = collect(Schema::getIndexes('vacancy_sources'))
            ->filter(static fn (array $index): bool => $index['unique'] && !$index['primary'])
            ->pluck('columns')
            ->all();

        $this->assertContains(['name'], $uniqueColumns);
    }
}
