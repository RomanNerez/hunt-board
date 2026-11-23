<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Actions;

use App\Containers\AppSection\Profile\Actions\DeleteProfileWorkExperienceAction;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteProfileWorkExperienceAction::class)]
final class DeleteProfileWorkExperienceActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesRecord(): void
    {
        $record = ProfileWorkExperience::factory()->createOne();
        $other = ProfileWorkExperience::factory()->createOne();

        $this->assertTrue(app(DeleteProfileWorkExperienceAction::class)->run($record->id));

        $this->assertModelMissing($record);
        $this->assertModelExists($other);
    }
}
