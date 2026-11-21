<?php

namespace App\Containers\AppSection\Status\Tests\Unit\Actions;

use App\Containers\AppSection\Status\Actions\DeleteStatusAction;
use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteStatusAction::class)]
final class DeleteStatusActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesStatus(): void
    {
        $status = Status::factory()->createOne();
        $otherStatus = Status::factory()->createOne();

        $result = app(DeleteStatusAction::class)->run($status->id);

        $this->assertTrue($result);
        $this->assertModelMissing($status);
        $this->assertModelExists($otherStatus);
    }
}
