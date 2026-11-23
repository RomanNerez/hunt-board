<?php

namespace App\Containers\AppSection\Stack\Tests\Unit\Actions;

use App\Containers\AppSection\Stack\Actions\CreateStackAction;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateStackAction::class)]
final class CreateStackActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesStack(): void
    {
        $record = app(CreateStackAction::class)->run(['name' => 'Laravel']);

        $this->assertDatabaseHas(Stack::class, ['id' => $record->id, 'name' => 'Laravel']);
    }
}
