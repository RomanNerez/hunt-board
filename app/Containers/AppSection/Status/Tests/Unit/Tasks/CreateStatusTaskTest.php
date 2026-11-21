<?php

namespace App\Containers\AppSection\Status\Tests\Unit\Tasks;

use App\Containers\AppSection\Status\Enums\StatusColor;
use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\Tasks\CreateStatusTask;
use App\Containers\AppSection\Status\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateStatusTask::class)]
final class CreateStatusTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesStatus(): void
    {
        $status = app(CreateStatusTask::class)->run([
            'name' => 'Interview',
            'color' => StatusColor::BLUE,
        ]);

        $this->assertSame('Interview', $status->name);
        $this->assertSame(StatusColor::BLUE, $status->color);
        $this->assertDatabaseHas(Status::class, [
            'id' => $status->id,
            'name' => 'Interview',
            'color' => StatusColor::BLUE,
        ]);
    }
}
