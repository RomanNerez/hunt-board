<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Pages;

use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileSkills;
use App\Containers\AppSection\User\Models\User;
use Filament\Forms\Components\Repeater;
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
        $groups = [
            ['name' => 'Backend', 'skills' => ['PHP', 'Laravel', 'Livewire']],
            ['name' => 'Frontend', 'skills' => ['Vue.js', 'React']],
        ];

        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => $groups])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Профіль збережено');

        $this->assertSame($groups, $this->storedGroups($this->user));
    }

    /**
     * @return void
     */
    public function testRenamesGroupKeepingSkills(): void
    {
        ProfileSkillGroup::factory()->createOne(['user_id' => $this->user->id, 'name' => 'Бази даних', 'skills' => ['PostgreSQL', 'Redis']]);

        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => [['name' => 'Бази даних & AI', 'skills' => ['PostgreSQL', 'Redis']]]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([['name' => 'Бази даних & AI', 'skills' => ['PostgreSQL', 'Redis']]], $this->storedGroups($this->user));
    }

    /**
     * @return void
     */
    public function testRemovingGroupKeepsTheOther(): void
    {
        $this->seedGroups();

        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => [['name' => 'Backend', 'skills' => ['PHP', 'Laravel', 'Node.js']]]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['Backend'], array_column($this->storedGroups($this->user), 'name'));
    }

    /**
     * @return void
     */
    public function testNewGroupOrderIsShownAfterReopening(): void
    {
        $this->seedGroups();

        Livewire::test(EditProfileSkills::class)
            ->assertSchemaStateSet(['groups' => [
                ['name' => 'Backend', 'skills' => ['PHP', 'Laravel', 'Node.js']],
                ['name' => 'Frontend', 'skills' => ['Vue.js', 'React', 'MUI']],
            ]])
            ->fillForm(['groups' => [
                ['name' => 'Frontend', 'skills' => ['Vue.js', 'React', 'MUI']],
                ['name' => 'Backend', 'skills' => ['PHP', 'Laravel', 'Node.js']],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditProfileSkills::class)
            ->assertSchemaStateSet(['groups' => [
                ['name' => 'Frontend', 'skills' => ['Vue.js', 'React', 'MUI']],
                ['name' => 'Backend', 'skills' => ['PHP', 'Laravel', 'Node.js']],
            ]]);
    }

    /**
     * @return void
     */
    public function testNewSkillOrderIsShownAfterReopening(): void
    {
        $this->seedGroups();

        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => [
                ['name' => 'Backend', 'skills' => ['Node.js', 'PHP', 'Laravel']],
                ['name' => 'Frontend', 'skills' => ['Vue.js', 'React', 'MUI']],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditProfileSkills::class)
            ->assertSchemaStateSet(['groups' => [
                ['name' => 'Backend', 'skills' => ['Node.js', 'PHP', 'Laravel']],
                ['name' => 'Frontend', 'skills' => ['Vue.js', 'React', 'MUI']],
            ]]);
    }

    /**
     * @return void
     */
    public function testRemovingSkillKeepsTheOthers(): void
    {
        $this->seedGroups();

        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => [
                ['name' => 'Backend', 'skills' => ['PHP', 'Laravel', 'Node.js']],
                ['name' => 'Frontend', 'skills' => ['Vue.js', 'React']],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['Vue.js', 'React'], $this->storedGroups($this->user)[1]['skills']);
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function invalidGroupProvider(): array
    {
        return [
            'missing name' => [['name' => '', 'skills' => ['Git']], 'name', 'required'],
            'no skills' => [['name' => 'Інструменти', 'skills' => []], 'skills', 'required'],
            'too long skill' => [['name' => 'Інструменти', 'skills' => [str_repeat('a', 101)]], 'skills.0', 'max'],
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

        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => [['name' => 'DevOps', 'skills' => ['Docker']], $invalidGroup]])
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
        ProfileSkillGroup::factory()->createOne(['user_id' => $other->id, 'name' => 'Backend']);
        $this->seedGroups();

        Livewire::test(EditProfileSkills::class)
            ->fillForm(['groups' => []])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([], $this->storedGroups($this->user));
        $this->assertSame(['Backend'], array_column($this->storedGroups($other), 'name'));
    }

    /**
     * @return void
     */
    private function seedGroups(): void
    {
        ProfileSkillGroup::factory()->createOne(['user_id' => $this->user->id, 'name' => 'Backend', 'skills' => ['PHP', 'Laravel', 'Node.js'], 'sort' => 0]);
        ProfileSkillGroup::factory()->createOne(['user_id' => $this->user->id, 'name' => 'Frontend', 'skills' => ['Vue.js', 'React', 'MUI'], 'sort' => 1]);
    }

    /**
     * @param User $user
     *
     * @return array<int, array{name: string, skills: list<string>}>
     */
    private function storedGroups(User $user): array
    {
        return ProfileSkillGroup::query()
            ->where('user_id', $user->id)
            ->orderBy('sort')
            ->get()
            ->map(static fn (ProfileSkillGroup $group): array => ['name' => $group->name, 'skills' => $group->skills])
            ->values()
            ->all();
    }
}
