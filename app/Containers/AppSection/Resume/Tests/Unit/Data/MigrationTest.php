<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Data;

use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
final class MigrationTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testResumesTableHasExpectedColumns(): void
    {
        $this->assertDatabaseTable('resumes', [
            'id' => 'int8',
            'user_id' => 'int8',
            'kind' => 'varchar',
            'first_name' => 'varchar',
            'last_name' => 'varchar',
            'headline' => 'varchar',
            'summary' => 'text',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ]);
    }

    /**
     * @return void
     */
    public function testDeletingUserDeletesItsResumes(): void
    {
        $user = User::factory()->createOne();
        $resumes = Resume::factory()->count(2)->createMany([['user_id' => $user->id], ['user_id' => $user->id]]);
        $otherResume = Resume::factory()->createOne();

        $user->delete();

        foreach ($resumes as $resume) {
            $this->assertModelMissing($resume);
        }

        $this->assertModelExists($otherResume);
    }
}
