<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Tasks;

use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Tasks\DeleteProfileProjectTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteProfileProjectTask::class)]
final class DeleteProfileProjectTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesOnlyGivenProject(): void
    {
        $project = ProfileProject::factory()->createOne();
        $other = ProfileProject::factory()->createOne();

        $this->assertTrue(app(DeleteProfileProjectTask::class)->run($project->id));

        $this->assertModelMissing($project);
        $this->assertModelExists($other);
    }
}
