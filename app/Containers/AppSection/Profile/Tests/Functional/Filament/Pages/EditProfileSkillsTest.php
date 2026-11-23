<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Pages;

use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileSkills;
use App\Containers\AppSection\Stack\Models\Stack;
use App\Containers\AppSection\User\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(EditProfileSkills::class)]
final class EditProfileSkillsTest extends FunctionalTestCase
{
    /**
     * @var User
     */
    private User $user;

    /**
     * @var \Closure
     */
    private \Closure $undoRepeaterFake;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->undoRepeaterFake = Repeater::fake();
        $this->user = User::factory()->createOne();
        $this->actingAs($this->user);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        ($this->undoRepeaterFake)();

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testAddsBackendAndFrontendGroups(): void
    {
        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => [
                ['name' => ['uk' => 'Backend'], 'stacks' => $this->ids('PHP', 'Laravel', 'Livewire')],
                ['name' => ['uk' => 'Frontend'], 'stacks' => $this->ids('Vue.js', 'React')],
            ]])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Профіль збережено');

        $this->assertSame([
            ['name' => 'Backend', 'stacks' => ['PHP', 'Laravel', 'Livewire']],
            ['name' => 'Frontend', 'stacks' => ['Vue.js', 'React']],
        ], $this->storedGroups($this->user));
    }

    /**
     * @return void
     */
    public function testOptionsComeFromSharedCatalogSortedByName(): void
    {
        $redis = Stack::factory()->createOne(['name' => 'Redis']);
        $docker = Stack::factory()->createOne(['name' => 'Docker']);

        $component = Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => [['name' => ['uk' => 'DevOps'], 'stacks' => []]]]);

        $component->assertFormFieldExists('groups.0.stacks', static fn (Select $field): bool => [$docker->id => 'Docker', $redis->id => 'Redis'] === $field->getOptions());
    }

    /**
     * @return void
     */
    public function testCreatesStackFromSkillsForm(): void
    {
        $php = Stack::factory()->createOne(['name' => 'PHP']);

        $component = Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => [['name' => ['uk' => 'Backend'], 'stacks' => [$php->id]]]])
            ->callAction(TestAction::make('createOption')->schemaComponent('groups.0.stacks'), data: ['name' => ' Node.js ']);

        $node = Stack::query()->where('name', 'Node.js')->firstOrFail();

        $component->assertSchemaStateSet(['groups.0.stacks' => [$php->id, $node->id]]);
    }

    /**
     * @return void
     */
    public function testCreatingStackFromFormRejectsTooLongName(): void
    {
        $this->seedGroups();

        Livewire::test(EditProfileSkills::class)
            ->callAction(TestAction::make('createOption')->schemaComponent('groups.0.stacks'), data: ['name' => str_repeat('a', 101)])
            ->assertHasFormErrors(['name' => 'max']);

        $this->assertDatabaseMissing(Stack::class, ['name' => str_repeat('a', 101)]);
        $this->assertSame(['Backend', 'Frontend'], array_column($this->storedGroups($this->user), 'name'));
    }

    /**
     * @return void
     */
    public function testRenamesGroupKeepingStacks(): void
    {
        $group = ProfileSkillGroup::factory()->createOne(['user_id' => $this->user->id, 'name' => ['uk' => 'Бази даних']]);
        $this->attach($group, 'PostgreSQL', 'Redis');

        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => [['name' => ['uk' => 'Бази даних & AI'], 'stacks' => $this->ids('PostgreSQL', 'Redis')]]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([['name' => 'Бази даних & AI', 'stacks' => ['PostgreSQL', 'Redis']]], $this->storedGroups($this->user));
    }

    /**
     * @return void
     */
    public function testRemovingGroupKeepsTheOtherAndStacks(): void
    {
        $this->seedGroups();

        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => [['name' => ['uk' => 'Backend'], 'stacks' => $this->ids('PHP', 'Laravel', 'Node.js')]]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['Backend'], array_column($this->storedGroups($this->user), 'name'));
        $this->assertDatabaseHas(Stack::class, ['name' => 'MUI']);
    }

    /**
     * @return void
     */
    public function testNewGroupOrderIsShownAfterReopening(): void
    {
        $this->seedGroups();
        $backend = $this->ids('PHP', 'Laravel', 'Node.js');
        $frontend = $this->ids('Vue.js', 'React', 'MUI');

        Livewire::test(EditProfileSkills::class)
            ->assertSchemaStateSet(['groups' => [
                ['name' => ['uk' => 'Backend', 'en' => null], 'stacks' => $backend],
                ['name' => ['uk' => 'Frontend', 'en' => null], 'stacks' => $frontend],
            ]])
            ->fillForm(['groups' => [
                ['name' => ['uk' => 'Frontend'], 'stacks' => $frontend],
                ['name' => ['uk' => 'Backend'], 'stacks' => $backend],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditProfileSkills::class)
            ->assertSchemaStateSet(['groups' => [
                ['name' => ['uk' => 'Frontend', 'en' => null], 'stacks' => $frontend],
                ['name' => ['uk' => 'Backend', 'en' => null], 'stacks' => $backend],
            ]]);
    }

    /**
     * @return void
     */
    public function testReselectedStackMovesToTheEndAfterReopening(): void
    {
        $this->seedGroups();

        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => [
                ['name' => ['uk' => 'Backend'], 'stacks' => $this->ids('Laravel', 'Node.js', 'PHP')],
                ['name' => ['uk' => 'Frontend'], 'stacks' => $this->ids('Vue.js', 'React', 'MUI')],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditProfileSkills::class)
            ->assertSchemaStateSet(['groups.0.stacks' => $this->ids('Laravel', 'Node.js', 'PHP')]);
        $this->assertSame(['Laravel', 'Node.js', 'PHP'], $this->storedGroups($this->user)[0]['stacks']);
    }

    /**
     * @return void
     */
    public function testRemovingStackKeepsTheOthersAndCatalog(): void
    {
        $this->seedGroups();

        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => [
                ['name' => ['uk' => 'Backend'], 'stacks' => $this->ids('PHP', 'Laravel', 'Node.js')],
                ['name' => ['uk' => 'Frontend'], 'stacks' => $this->ids('Vue.js', 'React')],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['Vue.js', 'React'], $this->storedGroups($this->user)[1]['stacks']);
        $this->assertDatabaseHas(Stack::class, ['name' => 'MUI']);
    }

    /**
     * @return void
     */
    public function testSameStackInTwoGroups(): void
    {
        $docker = $this->ids('Docker');

        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => [
                ['name' => ['uk' => 'DevOps'], 'stacks' => $docker],
                ['name' => ['uk' => 'Інструменти'], 'stacks' => $docker],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([
            ['name' => 'DevOps', 'stacks' => ['Docker']],
            ['name' => 'Інструменти', 'stacks' => ['Docker']],
        ], $this->storedGroups($this->user));
    }

    /**
     * @return void
     */
    public function testGroupNameIsKeptInBothLanguagesAndStacksDoNotDependOnLocale(): void
    {
        $stacks = $this->ids('Docker', 'Git');

        Livewire::test(EditProfileSkills::class)
            ->set('dataLocale', 'en')
            ->fillForm(['groups' => [['name' => ['uk' => 'Інструменти', 'en' => 'Tools'], 'stacks' => $stacks]]])
            ->call('save')
            ->assertHasNoFormErrors();

        $group = ProfileSkillGroup::query()->where('user_id', $this->user->id)->sole();

        $this->assertEquals(['uk' => 'Інструменти', 'en' => 'Tools'], $group->getTranslations('name'));
        $this->assertSame(['Docker', 'Git'], $group->stacks()->pluck('name')->all());

        Livewire::test(EditProfileSkills::class)
            ->assertSee('Інструменти')
            ->assertSchemaStateSet(['groups.0.stacks' => $stacks])
            ->set('dataLocale', 'en')
            ->assertSchemaStateSet(['groups.0.name.en' => 'Tools', 'groups.0.stacks' => $stacks])
            ->assertSee('Tools');
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function invalidGroupProvider(): array
    {
        return [
            'missing name' => [['name' => ['uk' => ''], 'stacks' => ['Git']], 'name.uk', 'required'],
            'english name only' => [['name' => ['uk' => '', 'en' => 'Tools'], 'stacks' => ['Git']], 'name.uk', 'required'],
            'too long english name' => [['name' => ['uk' => 'Інструменти', 'en' => str_repeat('a', 101)], 'stacks' => ['Git']], 'name.en', 'max'],
            'no stacks' => [['name' => ['uk' => 'Інструменти'], 'stacks' => []], 'stacks', 'required'],
        ];
    }

    /**
     * @param array<string, mixed> $invalidGroup
     * @param string $field
     * @param string $rule
     *
     * @return void
     */
    #[DataProvider('invalidGroupProvider')]
    public function testInvalidGroupKeepsStoredList(array $invalidGroup, string $field, string $rule): void
    {
        $this->seedGroups();
        /** @var list<string> $names */
        $names = $invalidGroup['stacks'];
        $invalidGroup['stacks'] = $this->ids(...$names);

        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => [['name' => ['uk' => 'DevOps'], 'stacks' => $this->ids('Docker')], $invalidGroup]])
            ->call('save')
            ->assertHasFormErrors(["groups.1.{$field}" => $rule])
            ->assertNotNotified('Профіль збережено');

        $this->assertSame(['Backend', 'Frontend'], array_column($this->storedGroups($this->user), 'name'));
    }

    /**
     * @return void
     */
    public function testSavingEmptyListDoesNotTouchAnotherUser(): void
    {
        $other = User::factory()->createOne();
        $this->attach(ProfileSkillGroup::factory()->createOne(['user_id' => $other->id, 'name' => 'Backend']), 'PHP');
        $this->seedGroups();

        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => []])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([], $this->storedGroups($this->user));
        $this->assertSame([['name' => 'Backend', 'stacks' => ['PHP']]], $this->storedGroups($other));
    }

    /**
     * @return void
     */
    private function seedGroups(): void
    {
        $this->attach(ProfileSkillGroup::factory()->createOne(['user_id' => $this->user->id, 'name' => 'Backend', 'sort' => 0]), 'PHP', 'Laravel', 'Node.js');
        $this->attach(ProfileSkillGroup::factory()->createOne(['user_id' => $this->user->id, 'name' => 'Frontend', 'sort' => 1]), 'Vue.js', 'React', 'MUI');
    }

    /**
     * @param ProfileSkillGroup $group
     * @param string ...$names
     *
     * @return void
     */
    private function attach(ProfileSkillGroup $group, string ...$names): void
    {
        foreach ($this->ids(...$names) as $sort => $id) {
            $group->stacks()->attach($id, ['sort' => $sort]);
        }
    }

    /**
     * @param string ...$names
     *
     * @return list<int>
     */
    private function ids(string ...$names): array
    {
        return array_map(static fn (string $name): int => Stack::query()->firstOrCreate(['name' => $name])->id, array_values($names));
    }

    /**
     * @param User $user
     *
     * @return array<int, array{name: string, stacks: list<string>}>
     */
    private function storedGroups(User $user): array
    {
        return ProfileSkillGroup::query()
            ->with('stacks')
            ->where('user_id', $user->id)
            ->orderBy('sort')
            ->get()
            ->map(static function (ProfileSkillGroup $group): array {
                /** @var list<string> $stacks */
                $stacks = $group->stacks->pluck('name')->values()->all();

                return ['name' => $group->name, 'stacks' => $stacks];
            })
            ->values()
            ->all();
    }
}
