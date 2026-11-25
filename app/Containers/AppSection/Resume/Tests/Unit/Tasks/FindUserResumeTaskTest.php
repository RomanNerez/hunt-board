<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Tasks;

use App\Containers\AppSection\Resume\Exceptions\ResumePdfUnavailable;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Containers\AppSection\Resume\Models\ResumeExperienceProject;
use App\Containers\AppSection\Resume\Tasks\FindUserResumeTask;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FindUserResumeTask::class)]
final class FindUserResumeTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testFindsOwnResumeWithSectionsLoaded(): void
    {
        $resume = Resume::factory()->createOne();
        ResumeExperienceProject::factory()->createOne([
            'resume_experience_id' => ResumeExperience::factory()->createOne(['resume_id' => $resume->id])->id,
        ]);

        $found = app(FindUserResumeTask::class)->run($resume->user_id, $resume->id);

        $this->assertTrue($found->is($resume));

        foreach (['links', 'experiences', 'educations', 'languages'] as $relation) {
            $this->assertTrue($found->relationLoaded($relation), $relation);
        }

        $experience = $found->experiences->sole();
        $this->assertTrue($experience->relationLoaded('projects'));
        $this->assertTrue($experience->projects->sole()->relationLoaded('stacks'));
    }

    /**
     * @return void
     */
    public function testForeignResumeIsNotFound(): void
    {
        $resume = Resume::factory()->createOne();
        $stranger = User::factory()->createOne();

        $this->assertNotFound(static fn (): Resume => app(FindUserResumeTask::class)->run($stranger->id, $resume->id));
    }

    /**
     * @return void
     */
    public function testMissingResumeIsNotFound(): void
    {
        $user = User::factory()->createOne();

        $this->assertNotFound(static fn (): Resume => app(FindUserResumeTask::class)->run($user->id, 999999));
    }

    /**
     * @param \Closure(): Resume $find
     *
     * @return void
     */
    private function assertNotFound(\Closure $find): void
    {
        try {
            $find();
            $this->fail('Exception expected');
        } catch (ResumePdfUnavailable $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }
    }
}
