<?php

namespace App\Containers\AppSection\Status\Tests\Unit\Actions;

use App\Containers\AppSection\Status\Actions\UpdateStatusAction;
use App\Containers\AppSection\Status\Enums\StatusColor;
use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateStatusAction::class)]
final class UpdateStatusActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUpdatesStatusNameAndColor(): void
    {
        $status = Status::factory()->createOne(['name' => 'Interview', 'color' => StatusColor::BLUE]);

        $result = app(UpdateStatusAction::class)->run($status->id, [
            'name' => 'Final interview',
            'color' => StatusColor::AMBER,
        ]);

        $this->assertSame('Final interview', $result->name);
        $this->assertSame(StatusColor::AMBER, $result->color);
        $this->assertDatabaseHas(Status::class, [
            'id' => $status->id,
            'name' => 'Final interview',
            'color' => StatusColor::AMBER,
        ]);
    }
}
