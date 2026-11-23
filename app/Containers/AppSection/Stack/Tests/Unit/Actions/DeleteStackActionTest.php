<?php

namespace App\Containers\AppSection\Stack\Tests\Unit\Actions;

use App\Containers\AppSection\Stack\Actions\DeleteStackAction;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteStackAction::class)]
final class DeleteStackActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesStack(): void
    {
        $record = Stack::factory()->createOne();
        $other = Stack::factory()->createOne();

        $this->assertTrue(app(DeleteStackAction::class)->run($record->id));

        $this->assertModelMissing($record);
        $this->assertModelExists($other);
    }
}
