<?php

namespace App\Containers\AppSection\Status\Tests\Unit\Actions;

use App\Containers\AppSection\Status\Actions\CreateStatusAction;
use App\Containers\AppSection\Status\Enums\StatusColor;
use App\Containers\AppSection\Status\Models\Status;
use App\Containers\AppSection\Status\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateStatusAction::class)]
final class CreateStatusActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesStatusWithNameAndColor(): void
    {
        $status = app(CreateStatusAction::class)->run([
            'name' => 'Interview',
            'color' => StatusColor::BLUE,
        ]);

        $this->assertDatabaseHas(Status::class, [
            'id' => $status->id,
            'name' => 'Interview',
            'color' => StatusColor::BLUE,
        ]);
    }
}
