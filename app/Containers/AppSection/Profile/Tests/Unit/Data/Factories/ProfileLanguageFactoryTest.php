<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Data\Factories;

use App\Containers\AppSection\Profile\Data\Factories\ProfileLanguageFactory;
use App\Containers\AppSection\Profile\Enums\LanguageLevel;
use App\Containers\AppSection\Profile\Models\ProfileLanguage;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProfileLanguageFactory::class)]
final class ProfileLanguageFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesLanguageWithKnownLevelAndNoDescription(): void
    {
        $record = ProfileLanguage::factory()->createOne()->refresh();

        $this->assertContains($record->level, LanguageLevel::cases());
        $this->assertNotSame('', $record->language);
        $this->assertNull($record->description);
    }
}
