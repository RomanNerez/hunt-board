<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Data\Migrations;

use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class MigrationTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUserProfilesTableHasOneRowPerUser(): void
    {
        $this->assertDatabaseTable('user_profiles', [
            'id' => 'int8',
            'user_id' => 'int8',
            'first_name' => 'varchar',
            'last_name' => 'varchar',
            'email' => 'varchar',
            'phone' => 'varchar',
            'location' => 'varchar',
            'headline' => 'varchar',
            'summary' => 'text',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
        $this->assertTrue($this->hasUniqueIndexOn('user_profiles', ['user_id']));
    }

    /**
     * @return void
     */
    public function testProfileLinksTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('profile_links', [
            'id' => 'int8',
            'user_id' => 'int8',
            'label' => 'varchar',
            'url' => 'varchar',
            'sort' => 'int2',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
    }

    /**
     * @return void
     */
    public function testProfileEducationsTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('profile_educations', [
            'id' => 'int8',
            'user_id' => 'int8',
            'type' => 'varchar',
            'title' => 'varchar',
            'institution' => 'varchar',
            'started_on' => 'date',
            'finished_on' => 'date',
            'url' => 'varchar',
            'description' => 'text',
            'sort' => 'int2',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
    }

    /**
     * @return void
     */
    public function testProfileLanguagesTableHasUniqueLanguagePerUser(): void
    {
        $this->assertDatabaseTable('profile_languages', [
            'id' => 'int8',
            'user_id' => 'int8',
            'language' => 'varchar',
            'level' => 'varchar',
            'sort' => 'int2',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
        $this->assertTrue($this->hasUniqueIndexOn('profile_languages', ['user_id', 'language']));
    }

    /**
     * @param string $table
     * @param list<string> $columns
     *
     * @return bool
     */
    private function hasUniqueIndexOn(string $table, array $columns): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if ($index['unique'] && $index['columns'] === $columns) {
                return true;
            }
        }

        return false;
    }
}
