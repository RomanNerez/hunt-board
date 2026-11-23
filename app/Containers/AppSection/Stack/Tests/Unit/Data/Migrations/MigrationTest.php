<?php

namespace App\Containers\AppSection\Stack\Tests\Unit\Data\Migrations;

use App\Containers\AppSection\Stack\Tests\UnitTestCase;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class MigrationTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testStacksTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('stacks', [
            'id' => 'int8',
            'name' => 'varchar',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
    }

    /**
     * @return void
     */
    public function testStackNamesAreUniqueIgnoringCase(): void
    {
        DB::table('stacks')->insert(['name' => 'PostgreSQL']);

        $this->expectException(UniqueConstraintViolationException::class);

        DB::table('stacks')->insert(['name' => 'postgresql']);
    }

    /**
     * @return void
     */
    public function testStackablesTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('stackables', [
            'stack_id' => 'int8',
            'stackable_type' => 'varchar',
            'stackable_id' => 'int8',
            'sort' => 'int2',
        ]);
    }

    /**
     * @return void
     */
    public function testStackCanBeAttachedToOwnerOnlyOnce(): void
    {
        $stackId = DB::table('stacks')->insertGetId(['name' => 'Laravel']);
        $this->attach($stackId, 0);

        $this->expectException(UniqueConstraintViolationException::class);

        $this->attach($stackId, 1);
    }

    /**
     * @return void
     */
    public function testStackablesAreDeletedWithStack(): void
    {
        $stackId = DB::table('stacks')->insertGetId(['name' => 'Laravel']);
        $this->attach($stackId, 0);

        DB::table('stacks')->where('id', $stackId)->delete();

        $this->assertDatabaseCount('stackables', 0);
    }

    /**
     * @param int $stackId
     * @param int $sort
     *
     * @return void
     */
    private function attach(int $stackId, int $sort): void
    {
        DB::table('stackables')->insert([
            'stack_id' => $stackId,
            'stackable_type' => 'owner',
            'stackable_id' => 1,
            'sort' => $sort,
        ]);
    }
}
