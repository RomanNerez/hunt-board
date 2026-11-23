<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Tasks;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tasks\DeleteProfileWorkExperienceTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\Stack\Models\Stack;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteProfileWorkExperienceTask::class)]
final class DeleteProfileWorkExperienceTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesOnlyGivenRecord(): void
    {
        $record = ProfileWorkExperience::factory()->createOne();
        $other = ProfileWorkExperience::factory()->createOne();

        $this->assertTrue(app(DeleteProfileWorkExperienceTask::class)->run($record->id));

        $this->assertModelMissing($record);
        $this->assertModelExists($other);
    }

    /**
     * @return void
     */
    public function testRemovesStackLinksOfItsProjects(): void
    {
        $record = ProfileWorkExperience::factory()->createOne();
        ProfileProject::factory()->withStacks(2)->count(2)->create(['work_experience_id' => $record->id]);
        $foreign = ProfileProject::factory()->withStacks(1)->createOne();

        app(DeleteProfileWorkExperienceTask::class)->run($record->id);

        $this->assertDatabaseCount('stackables', 1);
        $this->assertDatabaseHas('stackables', ['stackable_type' => 'profile_project', 'stackable_id' => $foreign->id]);
        $this->assertDatabaseCount(Stack::class, 5);
    }
}
