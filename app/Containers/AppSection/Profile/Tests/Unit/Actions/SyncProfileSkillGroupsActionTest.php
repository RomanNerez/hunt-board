<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Actions;

use App\Containers\AppSection\Profile\Actions\SyncProfileSkillGroupsAction;
use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Profile\Tasks\ReplaceProfileSkillGroupsTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Database\QueryException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SyncProfileSkillGroupsAction::class)]
#[CoversClass(ReplaceProfileSkillGroupsTask::class)]
final class SyncProfileSkillGroupsActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testAddsGroupsAndStacksInGivenOrder(): void
    {
        $user = User::factory()->createOne();
        [$php, $laravel, $livewire, $vue, $react] = $this->stacks('PHP', 'Laravel', 'Livewire', 'Vue.js', 'React');

        $groups = app(SyncProfileSkillGroupsAction::class)->run($user->id, [
            ['name' => 'Backend', 'stacks' => [$php->id, $laravel->id, $livewire->id]],
            ['name' => 'Frontend', 'stacks' => [(string) $vue->id, (string) $react->id]],
        ]);

        $this->assertSame(['Backend', 'Frontend'], $groups->pluck('name')->all());
        $this->assertSame([
            ['Backend', ['PHP', 'Laravel', 'Livewire'], 0],
            ['Frontend', ['Vue.js', 'React'], 1],
        ], $this->storedGroups($user->id));
    }

    /**
     * @return void
     */
    public function testKeepsFirstOfRepeatedStacks(): void
    {
        $user = User::factory()->createOne();
        [$docker, $redis] = $this->stacks('Docker', 'Redis');

        app(SyncProfileSkillGroupsAction::class)->run($user->id, [
            ['name' => 'Інструменти', 'stacks' => [$docker->id, $docker->id, $redis->id]],
        ]);

        $this->assertSame([['Інструменти', ['Docker', 'Redis'], 0]], $this->storedGroups($user->id));
    }

    /**
     * @return void
     */
    public function testRepeatedCallReplacesListAndOrder(): void
    {
        $user = User::factory()->createOne();
        [$php, $laravel, $node, $vue] = $this->stacks('PHP', 'Laravel', 'Node.js', 'Vue.js');
        $action = app(SyncProfileSkillGroupsAction::class);
        $action->run($user->id, [
            ['name' => 'Backend', 'stacks' => [$php->id, $laravel->id, $node->id]],
            ['name' => 'Frontend', 'stacks' => [$vue->id]],
        ]);

        $action->run($user->id, [
            ['name' => 'Frontend', 'stacks' => [$vue->id]],
            ['name' => 'Backend', 'stacks' => [$laravel->id, $node->id, $php->id]],
        ]);

        $this->assertSame([
            ['Frontend', ['Vue.js'], 0],
            ['Backend', ['Laravel', 'Node.js', 'PHP'], 1],
        ], $this->storedGroups($user->id));
        $this->assertDatabaseCount('stackables', 4);
    }

    /**
     * @return void
     */
    public function testSameStackInTwoGroups(): void
    {
        $user = User::factory()->createOne();
        [$docker] = $this->stacks('Docker');

        app(SyncProfileSkillGroupsAction::class)->run($user->id, [
            ['name' => 'DevOps', 'stacks' => [$docker->id]],
            ['name' => 'Інструменти', 'stacks' => [$docker->id]],
        ]);

        $this->assertSame([
            ['DevOps', ['Docker'], 0],
            ['Інструменти', ['Docker'], 1],
        ], $this->storedGroups($user->id));
        $this->assertDatabaseCount(Stack::class, 1);
    }

    /**
     * @return void
     */
    public function testRemovedGroupKeepsStackAndLeavesNoLinks(): void
    {
        $user = User::factory()->createOne();
        [$php, $docker] = $this->stacks('PHP', 'Docker');
        $action = app(SyncProfileSkillGroupsAction::class);
        $action->run($user->id, [
            ['name' => 'Backend', 'stacks' => [$php->id]],
            ['name' => 'DevOps', 'stacks' => [$docker->id]],
        ]);

        $action->run($user->id, [
            ['name' => 'Backend', 'stacks' => [$php->id]],
        ]);

        $this->assertModelExists($docker);
        $this->assertDatabaseMissing('stackables', ['stack_id' => $docker->id]);
        $this->assertDatabaseCount('stackables', 1);
    }

    /**
     * @return void
     */
    public function testInvalidStackKeepsExistingGroups(): void
    {
        $user = User::factory()->createOne();
        [$php] = $this->stacks('PHP');
        $action = app(SyncProfileSkillGroupsAction::class);
        $action->run($user->id, [['name' => 'Backend', 'stacks' => [$php->id]]]);

        try {
            $action->run($user->id, [['name' => 'Frontend', 'stacks' => [$php->id + 1000]]]);
            $this->fail('QueryException was not thrown.');
        } catch (QueryException) {
        }

        $this->assertSame([['Backend', ['PHP'], 0]], $this->storedGroups($user->id));
    }

    /**
     * @return void
     */
    public function testEmptyListClearsOnlyOwnGroups(): void
    {
        $user = User::factory()->createOne();
        ProfileSkillGroup::factory()->withStacks(2)->count(2)->create(['user_id' => $user->id]);
        $foreign = ProfileSkillGroup::factory()->withStacks(1)->createOne(['name' => 'Backend']);

        $groups = app(SyncProfileSkillGroupsAction::class)->run($user->id, []);

        $this->assertCount(0, $groups);
        $this->assertSame([], $this->storedGroups($user->id));
        $this->assertModelExists($foreign);
        $this->assertCount(1, $foreign->stacks()->get());
        $this->assertDatabaseCount('stackables', 1);
    }

    /**
     * @return void
     */
    public function testStoresGroupNameInBothLanguages(): void
    {
        $user = User::factory()->createOne();
        [$docker] = $this->stacks('Docker');

        app(SyncProfileSkillGroupsAction::class)->run($user->id, [
            ['name' => ['uk' => ' Інструменти ', 'en' => 'Tools'], 'stacks' => [$docker->id]],
        ]);

        $group = ProfileSkillGroup::query()->where('user_id', $user->id)->sole();

        $this->assertEquals(['uk' => 'Інструменти', 'en' => 'Tools'], $group->getTranslations('name'));
        $this->assertSame(['Docker'], $group->stacks()->pluck('name')->all());
    }

    /**
     * @param string ...$names
     *
     * @return list<Stack>
     */
    private function stacks(string ...$names): array
    {
        return array_map(static fn (string $name): Stack => Stack::factory()->createOne(['name' => $name]), array_values($names));
    }

    /**
     * @param int $userId
     *
     * @return array<int, array{string, list<string>, int}>
     */
    private function storedGroups(int $userId): array
    {
        return ProfileSkillGroup::query()
            ->with('stacks')
            ->where('user_id', $userId)
            ->orderBy('sort')
            ->get()
            ->map(static function (ProfileSkillGroup $group): array {
                /** @var list<string> $stacks */
                $stacks = $group->stacks->pluck('name')->values()->all();

                return [$group->name, $stacks, $group->sort];
            })
            ->values()
            ->all();
    }
}
