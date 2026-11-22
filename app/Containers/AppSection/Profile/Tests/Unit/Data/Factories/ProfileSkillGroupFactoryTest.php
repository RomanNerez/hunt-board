<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Data\Factories;

use App\Containers\AppSection\Profile\Data\Factories\ProfileSkillGroupFactory;
use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProfileSkillGroupFactory::class)]
final class ProfileSkillGroupFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesNamedGroupWithSkills(): void
    {
        $record = ProfileSkillGroup::factory()->createOne()->refresh();

        $this->assertNotSame('', $record->name);
        $this->assertNotEmpty($record->skills);
        $this->assertContainsOnly('string', $record->skills);
        $this->assertSame(0, $record->sort);
    }
}
