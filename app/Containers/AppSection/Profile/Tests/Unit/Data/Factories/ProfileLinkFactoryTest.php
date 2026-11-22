<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Data\Factories;

use App\Containers\AppSection\Profile\Data\Factories\ProfileLinkFactory;
use App\Containers\AppSection\Profile\Models\ProfileLink;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProfileLinkFactory::class)]
final class ProfileLinkFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesLinkWithHttpUrl(): void
    {
        $record = ProfileLink::factory()->createOne()->refresh();

        $this->assertNotSame('', $record->label);
        $this->assertMatchesRegularExpression('#^https?://#', $record->url);
        $this->assertSame(0, $record->sort);
    }
}
