<?php

namespace App\Containers\AppSection\Stack\Tests\Unit\Tasks;

use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\Stack\Tasks\SyncStacksTask;
use App\Containers\AppSection\Stack\Tests\Fixtures\StackOwner;
use App\Containers\AppSection\Stack\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SyncStacksTask::class)]
final class SyncStacksTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        StackOwner::createTable();
    }

    /**
     * @return void
     */
    public function testAttachesStacksInGivenOrder(): void
    {
        $owner = StackOwner::query()->create();
        [$php, $laravel, $node] = Stack::factory()->count(3)->create()->all();

        app(SyncStacksTask::class)->run($owner, [$node->id, (string) $php->id, $laravel->id]);

        $this->assertSame([$node->id, $php->id, $laravel->id], $this->stackIds($owner));
    }

    /**
     * @return void
     */
    public function testKeepsFirstOccurrenceOfRepeatedStack(): void
    {
        $owner = StackOwner::query()->create();
        [$docker, $redis] = Stack::factory()->count(2)->create()->all();

        app(SyncStacksTask::class)->run($owner, [$docker->id, $docker->id, $redis->id]);

        $this->assertSame([$docker->id, $redis->id], $this->stackIds($owner));
    }

    /**
     * @return void
     */
    public function testResyncChangesOrderAndDetachesMissing(): void
    {
        $owner = StackOwner::query()->create();
        [$vue, $react, $mui] = Stack::factory()->count(3)->create()->all();
        app(SyncStacksTask::class)->run($owner, [$vue->id, $react->id, $mui->id]);

        app(SyncStacksTask::class)->run($owner, [$react->id, $vue->id]);

        $this->assertSame([$react->id, $vue->id], $this->stackIds($owner));
        $this->assertModelExists($mui);
    }

    /**
     * @return void
     */
    public function testEmptyListDetachesAll(): void
    {
        $owner = StackOwner::query()->create();
        app(SyncStacksTask::class)->run($owner, [Stack::factory()->createOne()->id]);

        app(SyncStacksTask::class)->run($owner, []);

        $this->assertSame([], $this->stackIds($owner));
    }

    /**
     * @param StackOwner $owner
     *
     * @return list<int>
     */
    private function stackIds(StackOwner $owner): array
    {
        /** @var list<int> $ids */
        $ids = $owner->stacks()->pluck('stacks.id')->all();

        return $ids;
    }
}
