<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Pages;

use App\Containers\AppSection\Profile\Models\ProfileLink;
use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileLinks;
use App\Containers\AppSection\User\Models\User;
use Filament\Forms\Components\Repeater;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(EditProfileLinks::class)]
final class EditProfileLinksTest extends FunctionalTestCase
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
    public function testAddsLinkedInAndGitHub(): void
    {
        Livewire::test(EditProfileLinks::class)
            ->fillForm(['links' => [
                ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/roman'],
                ['label' => 'GitHub', 'url' => 'https://github.com/roman'],
            ]])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Профіль збережено');

        $this->assertSame(
            [['label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/roman'], ['label' => 'GitHub', 'url' => 'https://github.com/roman']],
            $this->storedLinks($this->user),
        );
    }

    /**
     * @return void
     */
    public function testAcceptsCustomLabel(): void
    {
        Livewire::test(EditProfileLinks::class)
            ->fillForm(['links' => [['label' => 'Блог', 'url' => 'https://roman.dev']]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([['label' => 'Блог', 'url' => 'https://roman.dev']], $this->storedLinks($this->user));
    }

    /**
     * @return void
     */
    public function testRemovingLinkKeepsTheOther(): void
    {
        $this->seedLinks();

        Livewire::test(EditProfileLinks::class)
            ->fillForm(['links' => [['label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/roman']]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([['label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/roman']], $this->storedLinks($this->user));
    }

    /**
     * @return void
     */
    public function testNewOrderIsShownAfterReopening(): void
    {
        $this->seedLinks();

        Livewire::test(EditProfileLinks::class)
            ->assertSchemaStateSet(['links' => [
                ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/roman'],
                ['label' => 'GitHub', 'url' => 'https://github.com/roman'],
            ]])
            ->fillForm(['links' => [
                ['label' => 'GitHub', 'url' => 'https://github.com/roman'],
                ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/roman'],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditProfileLinks::class)
            ->assertSchemaStateSet(['links' => [
                ['label' => 'GitHub', 'url' => 'https://github.com/roman'],
                ['label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/roman'],
            ]]);
    }

    /**
     * @return array<string, array{array<string, string>, string, string}>
     */
    public static function invalidLinkProvider(): array
    {
        return [
            'url without scheme' => [['label' => 'LinkedIn', 'url' => 'linkedin.com/in/roman'], 'url', 'url'],
            'ftp scheme' => [['label' => 'Файли', 'url' => 'ftp://files.example.com'], 'url', 'url'],
            'missing label' => [['label' => '', 'url' => 'https://roman.dev'], 'label', 'required'],
        ];
    }

    /**
     * @param array<string, string> $invalidLink
     * @param string $field
     * @param string $rule
     *
     * @return void
     */
    #[DataProvider('invalidLinkProvider')]
    public function testInvalidLinkKeepsStoredList(array $invalidLink, string $field, string $rule): void
    {
        $this->seedLinks();

        Livewire::test(EditProfileLinks::class)
            ->fillForm(['links' => [['label' => 'Telegram', 'url' => 'https://t.me/roman'], $invalidLink]])
            ->call('save')
            ->assertHasFormErrors(["links.1.{$field}" => $rule])
            ->assertNotNotified('Профіль збережено');

        $this->assertCount(2, $this->storedLinks($this->user));
    }

    /**
     * @return void
     */
    public function testSavingEmptyListDoesNotTouchAnotherUser(): void
    {
        $other = User::factory()->createOne();
        ProfileLink::factory()->createOne(['user_id' => $other->id, 'label' => 'GitHub']);
        $this->seedLinks();

        Livewire::test(EditProfileLinks::class)
            ->fillForm(['links' => []])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([], $this->storedLinks($this->user));
        $this->assertSame(['GitHub'], array_column($this->storedLinks($other), 'label'));
    }

    /**
     * @return void
     */
    private function seedLinks(): void
    {
        ProfileLink::factory()->createOne(['user_id' => $this->user->id, 'label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/roman', 'sort' => 0]);
        ProfileLink::factory()->createOne(['user_id' => $this->user->id, 'label' => 'GitHub', 'url' => 'https://github.com/roman', 'sort' => 1]);
    }

    /**
     * @param User $user
     *
     * @return array<int, array{label: string, url: string}>
     */
    private function storedLinks(User $user): array
    {
        return ProfileLink::query()
            ->where('user_id', $user->id)
            ->orderBy('sort')
            ->get()
            ->map(static fn (ProfileLink $link): array => ['label' => $link->label, 'url' => $link->url])
            ->values()
            ->all();
    }
}
