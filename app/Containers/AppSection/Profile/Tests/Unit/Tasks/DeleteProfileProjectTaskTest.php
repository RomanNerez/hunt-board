<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Tasks;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Tasks\DeleteProfileProjectTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\Stack\Models\Stack;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteProfileProjectTask::class)]
final class DeleteProfileProjectTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesOnlyGivenProject(): void
    {
        $project = ProfileProject::factory()->withStacks(2)->createOne();
        $other = ProfileProject::factory()->withStacks(1)->createOne();

        $this->assertTrue(app(DeleteProfileProjectTask::class)->run($project->id));

        $this->assertModelMissing($project);
        $this->assertModelExists($other);
        $this->assertDatabaseMissing('stackables', ['stackable_type' => 'profile_project', 'stackable_id' => $project->id]);
        $this->assertDatabaseCount('stackables', 1);
        $this->assertDatabaseCount(Stack::class, 3);
    }
}
