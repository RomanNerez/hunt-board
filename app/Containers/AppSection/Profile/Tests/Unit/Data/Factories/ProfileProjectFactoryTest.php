<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Data\Factories;

use App\Containers\AppSection\Profile\Data\Factories\ProfileProjectFactory;
use App\Containers\AppSection\Profile\Models\ProfileProject;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProfileProjectFactory::class)]
final class ProfileProjectFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesFinishedProjectWithStack(): void
    {
        $record = ProfileProject::factory()->createOne()->refresh();

        $this->assertNotSame('', $record->name);
        $this->assertNotEmpty($record->technologies);
        $this->assertContainsOnly('string', $record->technologies);
        $this->assertNotNull($record->finished_on);
        $this->assertTrue($record->finished_on->greaterThanOrEqualTo($record->started_on));
        $this->assertNull($record->description);
    }

    /**
     * @return void
     */
    public function testCurrentStateLeavesFinishDateEmpty(): void
    {
        $record = ProfileProject::factory()->current()->createOne()->refresh();

        $this->assertNull($record->finished_on);
    }

    /**
     * @return void
     */
    public function testWithDescriptionStateFillsBothLanguages(): void
    {
        $record = ProfileProject::factory()->withDescription()->createOne()->refresh();

        $translations = $record->getTranslations('description');

        $this->assertArrayHasKey('uk', $translations);
        $this->assertArrayHasKey('en', $translations);
    }
}
