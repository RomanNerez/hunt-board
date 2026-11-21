<?php

namespace App\Containers\AppSection\Status\Tests\Unit\Data\Factories;

use App\Containers\AppSection\Status\Data\Factories\StatusFactory;
use App\Containers\AppSection\Status\Enums\StatusColor;
use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(StatusFactory::class)]
final class StatusFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesStatusWithPaletteColor(): void
    {
        $status = Status::factory()->createOne();

        $this->assertContains($status->refresh()->color, StatusColor::cases());
    }
}
