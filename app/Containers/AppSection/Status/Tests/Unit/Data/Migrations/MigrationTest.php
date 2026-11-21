<?php

namespace App\Containers\AppSection\Status\Tests\Unit\Data\Migrations;

use App\Containers\AppSection\Status\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class MigrationTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testStatusesTableHasExpectedColumns(): void
    {
        $columns = [
            'id' => 'int8',
            'name' => 'varchar',
            'color' => 'varchar',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];

        $this->assertDatabaseTable('statuses', $columns);
    }
}
