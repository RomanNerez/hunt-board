<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Data\Factories;

use App\Containers\AppSection\Profile\Data\Factories\ProfileEducationFactory;
use App\Containers\AppSection\Profile\Enums\EducationType;
use App\Containers\AppSection\Profile\Models\ProfileEducation;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProfileEducationFactory::class)]
final class ProfileEducationFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesEducationWithKnownType(): void
    {
        $record = ProfileEducation::factory()->createOne()->refresh();

        $this->assertContains($record->type, EducationType::cases());
        $this->assertNotSame('', $record->title);
        $this->assertNull($record->started_on);
    }
}
