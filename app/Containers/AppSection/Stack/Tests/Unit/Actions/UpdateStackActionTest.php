<?php

namespace App\Containers\AppSection\Stack\Tests\Unit\Actions;

use App\Containers\AppSection\Stack\Actions\UpdateStackAction;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateStackAction::class)]
final class UpdateStackActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testRenamesStack(): void
    {
        $record = Stack::factory()->createOne(['name' => 'VueJS']);

        $updated = app(UpdateStackAction::class)->run($record->id, ['name' => 'Vue.js']);

        $this->assertSame('Vue.js', $updated->name);
        $this->assertDatabaseHas(Stack::class, ['id' => $record->id, 'name' => 'Vue.js']);
    }
}
