<?php

namespace App\Containers\AppSection\Status\Tests\Unit\Tasks;

use App\Containers\AppSection\Status\Enums\StatusColor;
use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\Tasks\UpdateStatusTask;
use App\Containers\AppSection\Status\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateStatusTask::class)]
final class UpdateStatusTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUpdatesStatusNameAndColor(): void
    {
        $status = Status::factory()->createOne(['name' => 'Interview', 'color' => StatusColor::BLUE]);

        $result = app(UpdateStatusTask::class)->run($status->id, [
            'name' => 'Final interview',
            'color' => StatusColor::AMBER,
        ]);

        $this->assertSame($status->id, $result->id);
        $this->assertDatabaseHas(Status::class, [
            'id' => $status->id,
            'name' => 'Final interview',
            'color' => StatusColor::AMBER,
        ]);
    }
}
