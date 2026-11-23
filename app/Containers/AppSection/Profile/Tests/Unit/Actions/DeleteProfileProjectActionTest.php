<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Actions;

use App\Containers\AppSection\Profile\Actions\DeleteProfileProjectAction;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteProfileProjectAction::class)]
final class DeleteProfileProjectActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesProject(): void
    {
        $project = ProfileProject::factory()->createOne();
        $other = ProfileProject::factory()->createOne();

        $this->assertTrue(app(DeleteProfileProjectAction::class)->run($project->id));

        $this->assertModelMissing($project);
        $this->assertModelExists($other);
    }
}
