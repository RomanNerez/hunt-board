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
    public function testCreatesNamedGroupWithoutStacks(): void
    {
        $record = ProfileSkillGroup::factory()->createOne()->refresh();

        $this->assertNotSame('', $record->name);
        $this->assertSame(0, $record->sort);
        $this->assertCount(0, $record->stacks()->get());
    }

    /**
     * @return void
     */
    public function testWithStacksStateAttachesOrderedStacks(): void
    {
        $record = ProfileSkillGroup::factory()->withStacks(3)->createOne();

        $this->assertSame([0, 1, 2], $record->stacks()->get()->pluck('pivot.sort')->all());
    }
}
