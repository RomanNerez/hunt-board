<?php

namespace App\Containers\AppSection\Resume\Tests\Unit\Tasks;

use App\Containers\AppSection\Resume\Enums\ResumeEmploymentType;
use App\Containers\AppSection\Resume\Models\Resume;
use App\Containers\AppSection\Resume\Models\ResumeExperience;
use App\Containers\AppSection\Resume\Models\ResumeExperienceProject;
use App\Containers\AppSection\Resume\Tasks\ReplaceResumeExperiencesTask;
use App\Containers\AppSection\Resume\Tests\UnitTestCase;
use App\Containers\AppSection\Stack\Models\Stack;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReplaceResumeExperiencesTask::class)]
final class ReplaceResumeExperiencesTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testReplacesListWithPositionAsSortOrderAndNormalizedDates(): void
    {
        $resume = Resume::factory()->createOne();
        ResumeExperience::factory()->createOne(['resume_id' => $resume->id, 'company' => 'Старе']);

        app(ReplaceResumeExperiencesTask::class)->run($resume, [
            [
                'company' => 'MacPaw',
                'company_url' => 'https://macpaw.com',
                'position' => 'Senior PHP-розробник',
                'location' => 'Київ',
                'employment_type' => 'full_time',
                'start_date' => '2021-03-17',
                'end_date' => '2024-08-20',
                'is_visible' => false,
            ],
            [
                'company' => 'Grammarly',
                'company_url' => '',
                'position' => 'Dev',
                'location' => '',
                'employment_type' => null,
                'start_date' => '2019-01-01',
                'end_date' => null,
            ],
        ]);

        $experiences = $resume->experiences()->get();

        $this->assertSame(['MacPaw', 'Grammarly'], $experiences->pluck('company')->all());
        $this->assertSame([0, 1], $experiences->pluck('sort_order')->all());
        $this->assertSame([false, true], $experiences->pluck('is_visible')->all());
        $this->assertSame('2021-03-01', $experiences[0]->start_date->toDateString());
        $this->assertSame('2024-08-01', $experiences[0]->end_date?->toDateString());
        $this->assertSame(ResumeEmploymentType::FullTime, $experiences[0]->employment_type);
        $this->assertNull($experiences[1]->end_date);
        $this->assertNull($experiences[1]->company_url);
        $this->assertNull($experiences[1]->location);
        $this->assertNull($experiences[1]->employment_type);
    }

    /**
     * @return void
     */
    public function testDoesNotTouchExperiencesOfOtherResumes(): void
    {
        $resume = Resume::factory()->createOne();
        $other = ResumeExperience::factory()->createOne();

        app(ReplaceResumeExperiencesTask::class)->run($resume, []);

        $this->assertModelExists($other);
    }

    /**
     * @return void
     */
    public function testRemovesProjectsAndTheirStacksOnlyOfThisResume(): void
    {
        $stack = Stack::factory()->createOne();
        $resume = Resume::factory()->createOne();
        $project = ResumeExperienceProject::factory()->createOne([
            'resume_experience_id' => ResumeExperience::factory()->createOne(['resume_id' => $resume->id])->id,
        ]);
        $project->stacks()->attach($stack->id, ['sort' => 0]);
        $otherProject = ResumeExperienceProject::factory()->createOne();
        $otherProject->stacks()->attach($stack->id, ['sort' => 0]);

        app(ReplaceResumeExperiencesTask::class)->run($resume, []);

        $this->assertModelMissing($project);
        $this->assertModelExists($otherProject);
        $this->assertDatabaseMissing('stackables', ['stackable_type' => $project->getMorphClass(), 'stackable_id' => $project->id]);
        $this->assertDatabaseHas('stackables', ['stackable_type' => $otherProject->getMorphClass(), 'stackable_id' => $otherProject->id]);
    }
}
