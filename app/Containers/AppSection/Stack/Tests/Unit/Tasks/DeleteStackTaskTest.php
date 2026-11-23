<?php

namespace App\Containers\AppSection\Stack\Tests\Unit\Tasks;

use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\Tasks\DeleteStackTask;
use App\Containers\AppSection\Stack\Tests\Fixtures\StackOwner;
use App\Containers\AppSection\Stack\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeleteStackTask::class)]
final class DeleteStackTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testDeletesStackAndKeepsOrderOfOthers(): void
    {
        StackOwner::createTable();
        $owner = StackOwner::query()->create();
        [$postgres, $mysql, $jquery] = Stack::factory()->count(3)->create()->all();
        $owner->stacks()->attach([
            $postgres->id => ['sort' => 0],
            $mysql->id => ['sort' => 1],
            $jquery->id => ['sort' => 2],
        ]);

        app(DeleteStackTask::class)->run($mysql->id);

        $this->assertModelMissing($mysql);
        $this->assertModelExists($owner);
        $this->assertSame([$postgres->id, $jquery->id], $owner->stacks()->pluck('stacks.id')->all());
    }
}
