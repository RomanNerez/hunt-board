<?php

namespace App\Containers\AppSection\Status\Tests\Unit\Tasks;

use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\Tasks\DeleteStatusTask;
use App\Containers\AppSection\Status\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteStatusTask::class)]
final class DeleteStatusTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesStatus(): void
    {
        $status = Status::factory()->createOne();

        $result = app(DeleteStatusTask::class)->run($status->id);

        $this->assertTrue($result);
        $this->assertModelMissing($status);
    }
}
