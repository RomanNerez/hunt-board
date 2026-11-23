<?php

namespace App\Containers\AppSection\Stack\Tests\Unit\Data\Factories;

use App\Containers\AppSection\Stack\Data\Factories\StackFactory;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(StackFactory::class)]
final class StackFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesStacksWithDistinctNames(): void
    {
        $records = Stack::factory()->count(3)->create();

        $this->assertDatabaseCount(Stack::class, 3);
        $this->assertCount(3, $records->pluck('name')->unique());
    }
}
