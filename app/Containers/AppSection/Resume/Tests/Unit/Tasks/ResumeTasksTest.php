<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Tasks;

use App\Containers\AppSection\Resume\Enums\ResumeKind;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Tasks\CreateResumeTask;
use App\Containers\AppSection\Resume\Tasks\DeleteResumeTask;
use App\Containers\AppSection\Resume\Tasks\UpdateResumeTask;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateResumeTask::class)]
#[CoversClass(UpdateResumeTask::class)]
#[CoversClass(DeleteResumeTask::class)]
final class ResumeTasksTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreateAssignsGivenOwnerAndIgnoresUserIdInData(): void
    {
        $owner = User::factory()->createOne();
        $other = User::factory()->createOne();

        $resume = app(CreateResumeTask::class)->run($owner->id, [
            'user_id' => $other->id,
            'kind' => ResumeKind::Application,
            'first_name' => 'Роман',
            'last_name' => 'Нерез',
        ]);

        $this->assertSame($owner->id, $resume->user_id);
        $this->assertSame(ResumeKind::Application, $resume->kind);
        $this->assertNull($resume->headline);
    }

    /**
     * @return void
     */
    public function testUpdateChangesAllowedFieldsAndKeepsOwner(): void
    {
        $resume = Resume::factory()->master()->createOne();
        $owner = $resume->user_id;
        $other = User::factory()->createOne();

        $updated = app(UpdateResumeTask::class)->run($resume->id, [
            'user_id' => $other->id,
            'kind' => ResumeKind::Application,
            'headline' => 'PHP Developer',
        ]);

        $this->assertSame($owner, $updated->user_id);
        $this->assertSame(ResumeKind::Application, $updated->kind);
        $this->assertSame('PHP Developer', $updated->headline);
    }

    /**
     * @return void
     */
    public function testDeleteRemovesResume(): void
    {
        $resume = Resume::factory()->createOne();

        $this->assertTrue(app(DeleteResumeTask::class)->run($resume->id));
        $this->assertModelMissing($resume);
    }
}
