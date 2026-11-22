<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Actions;

use App\Containers\AppSection\Profile\Actions\SyncProfileSkillGroupsAction;
use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Profile\Tasks\ReplaceProfileSkillGroupsTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SyncProfileSkillGroupsAction::class)]
#[CoversClass(ReplaceProfileSkillGroupsTask::class)]
final class SyncProfileSkillGroupsActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testAddsGroupsAndSkillsInGivenOrder(): void
    {
        $user = User::factory()->createOne();

        $groups = app(SyncProfileSkillGroupsAction::class)->run($user->id, [
            ['name' => 'Backend', 'skills' => ['PHP', 'Laravel', 'Livewire']],
            ['name' => 'Frontend', 'skills' => ['Vue.js', 'React']],
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
    public function testTrimsSkillsAndKeepsFirstOfDuplicates(): void
    {
        $user = User::factory()->createOne();

        app(SyncProfileSkillGroupsAction::class)->run($user->id, [
            ['name' => 'Інструменти', 'skills' => [' Docker ', 'Docker', '  ', 'Redis']],
        ]);

        $this->assertSame([['Інструменти', ['Docker', 'Redis'], 0]], $this->storedGroups($user->id));
    }

    /**
     * @return void
     */
    public function testRepeatedCallReplacesList(): void
    {
        $user = User::factory()->createOne();
        $action = app(SyncProfileSkillGroupsAction::class);
        $action->run($user->id, [
            ['name' => 'Backend', 'skills' => ['PHP', 'Laravel', 'Node.js']],
            ['name' => 'Frontend', 'skills' => ['Vue.js']],
        ]);

        $action->run($user->id, [
            ['name' => 'Frontend', 'skills' => ['Vue.js']],
            ['name' => 'Backend', 'skills' => ['Node.js', 'PHP', 'Laravel']],
        ]);

        $this->assertSame([
            ['Frontend', ['Vue.js'], 0],
            ['Backend', ['Node.js', 'PHP', 'Laravel'], 1],
        ], $this->storedGroups($user->id));
    }

    /**
     * @return void
     */
    public function testEmptyListClearsOnlyOwnGroups(): void
    {
        $user = User::factory()->createOne();
        ProfileSkillGroup::factory()->count(2)->create(['user_id' => $user->id]);
        $foreign = ProfileSkillGroup::factory()->createOne(['name' => 'Backend']);

        $groups = app(SyncProfileSkillGroupsAction::class)->run($user->id, []);

        $this->assertCount(0, $groups);
        $this->assertSame([], $this->storedGroups($user->id));
        $this->assertModelExists($foreign);
    }

    /**
     * @return void
     */
    public function testStoresGroupNameInBothLanguages(): void
    {
        $user = User::factory()->createOne();

        app(SyncProfileSkillGroupsAction::class)->run($user->id, [
            ['name' => ['uk' => ' Інструменти ', 'en' => 'Tools'], 'skills' => ['Docker']],
        ]);

        $group = ProfileSkillGroup::query()->where('user_id', $user->id)->sole();

        $this->assertEquals(['uk' => 'Інструменти', 'en' => 'Tools'], $group->getTranslations('name'));
        $this->assertSame(['Docker'], $group->skills);
    }

    /**
     * @param int $userId
     *
     * @return array<int, array{string, list<string>, int}>
     */
    private function storedGroups(int $userId): array
    {
        return ProfileSkillGroup::query()
            ->where('user_id', $userId)
            ->orderBy('sort')
            ->get()
            ->map(static fn (ProfileSkillGroup $group): array => [$group->name, $group->skills, $group->sort])
            ->values()
            ->all();
    }
}
