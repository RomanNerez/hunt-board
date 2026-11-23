<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Tasks;

use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tasks\DeleteProfileWorkExperienceTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
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
}
