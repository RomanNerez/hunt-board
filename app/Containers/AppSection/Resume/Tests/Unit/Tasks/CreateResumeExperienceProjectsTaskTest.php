<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Tasks;

use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Containers\AppSection\Resume\Models\ResumeExperienceProject;
use App\Containers\AppSection\Resume\Tasks\CreateResumeExperienceProjectsTask;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateResumeExperienceProjectsTask::class)]
final class CreateResumeExperienceProjectsTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesProjectsWithPositionAsSortOrderAndNormalizedFields(): void
    {
        $experience = ResumeExperience::factory()->createOne();

        $created = app(CreateResumeExperienceProjectsTask::class)->run($experience, [
            ['name' => 'Equally', 'role' => 'Tech Lead', 'description' => '<p>Опис</p>', 'is_visible' => false],
            ['name' => 'PippoSAS', 'role' => '', 'description' => ''],
        ]);

        $projects = $experience->projects()->get();

        $this->assertSame($projects->modelKeys(), array_map(static fn (ResumeExperienceProject $project): int => $project->id, $created));
        $this->assertSame(['Equally', 'PippoSAS'], $projects->pluck('name')->all());
        $this->assertSame([0, 1], $projects->pluck('sort_order')->all());
        $this->assertSame([false, true], $projects->pluck('is_visible')->all());
        $this->assertSame('Tech Lead', $projects[0]->role);
        $this->assertSame('<p>Опис</p>', $projects[0]->description);
        $this->assertNull($projects[1]->role);
        $this->assertNull($projects[1]->description);
    }

    /**
     * @return void
     */
    public function testEmptyListCreatesNothing(): void
    {
        $experience = ResumeExperience::factory()->createOne();

        $this->assertSame([], app(CreateResumeExperienceProjectsTask::class)->run($experience, []));
        $this->assertSame(0, $experience->projects()->count());
    }
}
