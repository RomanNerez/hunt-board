<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Data\Migrations;

use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\DB;
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
            'first_name' => 'jsonb',
            'last_name' => 'jsonb',
            'email' => 'varchar',
            'phone' => 'varchar',
            'location' => 'jsonb',
            'headline' => 'jsonb',
            'summary' => 'jsonb',
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
            'label' => 'jsonb',
            'url' => 'jsonb',
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
            'title' => 'jsonb',
            'institution' => 'jsonb',
            'started_on' => 'date',
            'finished_on' => 'date',
            'url' => 'varchar',
            'description' => 'jsonb',
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
            'language' => 'jsonb',
            'level' => 'varchar',
            'description' => 'jsonb',
            'sort' => 'int2',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
        $this->assertTrue($this->hasUniqueIndexOn('profile_languages', ['user_id', 'language']));
    }

    /**
     * @return void
     */
    public function testProfileSkillGroupsTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('profile_skill_groups', [
            'id' => 'int8',
            'user_id' => 'int8',
            'name' => 'jsonb',
            'skills' => 'json',
            'sort' => 'int2',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
    }

    /**
     * @return void
     */
    public function testProfileWorkExperiencesTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('profile_work_experiences', [
            'id' => 'int8',
            'user_id' => 'int8',
            'type' => 'varchar',
            'company_name' => 'jsonb',
            'role' => 'jsonb',
            'started_on' => 'date',
            'finished_on' => 'date',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
        $this->assertFalse(Schema::hasColumn('profile_work_experiences', 'sort'));
        $this->assertTrue($this->hasIndexOn('profile_work_experiences', ['user_id', 'started_on']));
        $this->assertSame(['finished_on'], $this->nullableColumns('profile_work_experiences', ['type', 'company_name', 'role', 'started_on', 'finished_on']));
    }

    /**
     * @return void
     */
    public function testProfileWorkExperiencesAreDeletedWithUser(): void
    {
        $user = User::factory()->createOne();
        DB::table('profile_work_experiences')->insert([
            'user_id' => $user->id,
            'type' => 'company',
            'company_name' => '{"uk": "MacPaw"}',
            'role' => '{"uk": "PHP-розробник"}',
            'started_on' => '2021-03-01',
        ]);

        $user->delete();

        $this->assertDatabaseCount('profile_work_experiences', 0);
    }

    /**
     * @return void
     */
    public function testProfileProjectsTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('profile_projects', [
            'id' => 'int8',
            'work_experience_id' => 'int8',
            'name' => 'jsonb',
            'started_on' => 'date',
            'finished_on' => 'date',
            'technologies' => 'json',
            'description' => 'jsonb',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
        $this->assertFalse(Schema::hasColumn('profile_projects', 'user_id'));
        $this->assertTrue($this->hasIndexOn('profile_projects', ['work_experience_id', 'started_on']));
        $this->assertSame(
            ['finished_on', 'description'],
            $this->nullableColumns('profile_projects', ['work_experience_id', 'name', 'started_on', 'finished_on', 'technologies', 'description']),
        );
    }

    /**
     * @return void
     */
    public function testProfileProjectsAreDeletedWithWorkExperience(): void
    {
        $user = User::factory()->createOne();
        $workExperienceId = $this->insertWorkExperience($user->id);
        $this->insertProject($workExperienceId);

        DB::table('profile_work_experiences')->where('id', $workExperienceId)->delete();

        $this->assertDatabaseCount('profile_projects', 0);
    }

    /**
     * @return void
     */
    public function testProfileProjectsAreDeletedWithUser(): void
    {
        $user = User::factory()->createOne();
        $this->insertProject($this->insertWorkExperience($user->id));

        $user->delete();

        $this->assertDatabaseCount('profile_projects', 0);
    }

    /**
     * @param int $userId
     *
     * @return int
     */
    private function insertWorkExperience(int $userId): int
    {
        return DB::table('profile_work_experiences')->insertGetId([
            'user_id' => $userId,
            'type' => 'company',
            'company_name' => '{"uk": "VARTEQ Inc."}',
            'role' => '{"uk": "Junior розробник"}',
            'started_on' => '2020-08-29',
        ]);
    }

    /**
     * @param int $workExperienceId
     *
     * @return void
     */
    private function insertProject(int $workExperienceId): void
    {
        DB::table('profile_projects')->insert([
            'work_experience_id' => $workExperienceId,
            'name' => '{"uk": "Аукціон з продажу автомобілів"}',
            'started_on' => '2020-08-29',
            'technologies' => '["PostgreSQL"]',
        ]);
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

    /**
     * @param string $table
     * @param list<string> $columns
     *
     * @return bool
     */
    private function hasIndexOn(string $table, array $columns): bool
    {
        foreach (Schema::getIndexes($table) as $index) {
            if ($index['columns'] === $columns) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $table
     * @param list<string> $columns
     *
     * @return list<string>
     */
    private function nullableColumns(string $table, array $columns): array
    {
        $nullable = [];

        foreach (Schema::getColumns($table) as $column) {
            if ($column['nullable'] && in_array($column['name'], $columns, true)) {
                $nullable[] = $column['name'];
            }
        }

        return $nullable;
    }
}
