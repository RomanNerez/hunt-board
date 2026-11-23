<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Actions;

use App\Containers\AppSection\Profile\Actions\UpdateProfileWorkExperienceAction;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateProfileWorkExperienceAction::class)]
final class UpdateProfileWorkExperienceActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUpdatesRoleOfRecord(): void
    {
        $record = ProfileWorkExperience::factory()->createOne(['company_name' => 'MacPaw', 'role' => 'PHP-розробник']);

        $result = app(UpdateProfileWorkExperienceAction::class)->run($record->id, ['role' => ['uk' => 'Senior PHP-розробник']]);

        $this->assertSame($record->id, $result->id);
        $this->assertSame('Senior PHP-розробник', $result->refresh()->role);
        $this->assertSame('MacPaw', $result->company_name);
    }

    /**
     * @return void
     */
    public function testNarrowingPeriodKeepsProjectsUnchanged(): void
    {
        $record = ProfileWorkExperience::factory()->createOne(['started_on' => '2019-09-03', 'finished_on' => '2020-08-21']);
        $project = ProfileProject::factory()->createOne(['work_experience_id' => $record->id, 'started_on' => '2019-09-03', 'finished_on' => '2020-08-21']);

        app(UpdateProfileWorkExperienceAction::class)->run($record->id, ['started_on' => '2019-10-01', 'finished_on' => '2020-08-01']);

        $this->assertSame('2020-08-01', $record->refresh()->finished_on?->toDateString());
        $project->refresh();
        $this->assertSame('2019-09-03', $project->started_on->toDateString());
        $this->assertSame('2020-08-21', $project->finished_on?->toDateString());
    }
}
