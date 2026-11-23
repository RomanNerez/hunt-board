<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Data\Factories;

use App\Containers\AppSection\Profile\Data\Factories\ProfileWorkExperienceFactory;
use App\Containers\AppSection\Profile\Enums\WorkExperienceType;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProfileWorkExperienceFactory::class)]
final class ProfileWorkExperienceFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesFinishedCompanyExperience(): void
    {
        $record = ProfileWorkExperience::factory()->createOne()->refresh();

        $this->assertSame(WorkExperienceType::COMPANY, $record->type);
        $this->assertNotSame('', $record->company_name);
        $this->assertNotSame('', $record->role);
        $this->assertNotNull($record->finished_on);
        $this->assertTrue($record->finished_on->greaterThanOrEqualTo($record->started_on));
    }

    /**
     * @return void
     */
    public function testFreelanceStateSetsFreelanceType(): void
    {
        $record = ProfileWorkExperience::factory()->freelance()->createOne()->refresh();

        $this->assertSame(WorkExperienceType::FREELANCE, $record->type);
    }

    /**
     * @return void
     */
    public function testCurrentStateLeavesFinishDateEmpty(): void
    {
        $record = ProfileWorkExperience::factory()->current()->createOne()->refresh();

        $this->assertNull($record->finished_on);
    }
}
