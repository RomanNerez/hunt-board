<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Pages;

use App\Containers\AppSection\Profile\Models\ProfileLink;
use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileLinks;
use App\Containers\AppSection\User\Models\User;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Facades\App;
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
                ['label' => ['uk' => 'LinkedIn'], 'url' => ['uk' => 'https://linkedin.com/in/roman']],
                ['label' => ['uk' => 'GitHub'], 'url' => ['uk' => 'https://github.com/roman']],
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
            ->fillForm(['links' => [['label' => ['uk' => 'Блог'], 'url' => ['uk' => 'https://roman.dev']]]])
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
            ->fillForm(['links' => [['label' => ['uk' => 'LinkedIn'], 'url' => ['uk' => 'https://linkedin.com/in/roman']]]])
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
                ['label' => ['uk' => 'LinkedIn', 'en' => null], 'url' => ['uk' => 'https://linkedin.com/in/roman', 'en' => null]],
                ['label' => ['uk' => 'GitHub', 'en' => null], 'url' => ['uk' => 'https://github.com/roman', 'en' => null]],
            ]])
            ->fillForm(['links' => [
                ['label' => ['uk' => 'GitHub'], 'url' => ['uk' => 'https://github.com/roman']],
                ['label' => ['uk' => 'LinkedIn'], 'url' => ['uk' => 'https://linkedin.com/in/roman']],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditProfileLinks::class)
            ->assertSchemaStateSet(['links' => [
                ['label' => ['uk' => 'GitHub', 'en' => null], 'url' => ['uk' => 'https://github.com/roman', 'en' => null]],
                ['label' => ['uk' => 'LinkedIn', 'en' => null], 'url' => ['uk' => 'https://linkedin.com/in/roman', 'en' => null]],
            ]]);
    }

    /**
     * @return void
     */
    public function testLinkWithoutEnglishLabelIsSaved(): void
    {
        Livewire::test(EditProfileLinks::class)
            ->fillForm(['links' => [['label' => ['uk' => 'Портфоліо', 'en' => ''], 'url' => ['uk' => 'https://example.com']]]])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Профіль збережено');

        $link = ProfileLink::query()->where('user_id', $this->user->id)->sole();

        $this->assertSame(['uk' => 'Портфоліо'], $link->getTranslations('label'));
        $this->assertSame(['uk' => 'https://example.com'], $link->getTranslations('url'));
    }

    /**
     * @return void
     */
    public function testLabelIsKeptInBothLanguagesWithSharedUrl(): void
    {
        Livewire::test(EditProfileLinks::class)
            ->fillForm(['links' => [['label' => ['uk' => 'Портфоліо', 'en' => 'Portfolio'], 'url' => ['uk' => 'https://example.com']]]])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditProfileLinks::class)
            ->set('dataLocale', 'en')
            ->assertSchemaStateSet(['links.0.label.en' => 'Portfolio', 'links.0.label.uk' => 'Портфоліо', 'links.0.url.uk' => 'https://example.com', 'links.0.url.en' => null]);
    }

    /**
     * @return void
     */
    public function testMissingUkrainianLabelKeepsListAndSwitchesToUkrainian(): void
    {
        $this->seedLinks();

        Livewire::test(EditProfileLinks::class)
            ->set('dataLocale', 'en')
            ->fillForm(['links' => [['label' => ['uk' => '', 'en' => 'Portfolio'], 'url' => ['uk' => 'https://example.com']]]])
            ->call('save')
            ->assertHasFormErrors(['links.0.label.uk' => 'required'])
            ->assertSet('dataLocale', 'uk');

        $this->assertSame(['LinkedIn', 'GitHub'], array_column($this->storedLinks($this->user), 'label'));
    }

    /**
     * @return void
     */
    public function testEnglishUrlIsKeptSeparately(): void
    {
        Livewire::test(EditProfileLinks::class)
            ->set('dataLocale', 'en')
            ->fillForm(['links' => [[
                'label' => ['uk' => 'LinkedIn'],
                'url' => ['uk' => 'https://www.linkedin.com/in/roman', 'en' => 'https://www.linkedin.com/in/roman/?locale=en_US'],
            ]]])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditProfileLinks::class)
            ->assertSchemaStateSet([
                'links.0.url.uk' => 'https://www.linkedin.com/in/roman',
                'links.0.url.en' => 'https://www.linkedin.com/in/roman/?locale=en_US',
            ]);
    }

    /**
     * @return void
     */
    public function testLinkWithoutEnglishUrlFallsBackToUkrainianUrl(): void
    {
        Livewire::test(EditProfileLinks::class)
            ->fillForm(['links' => [['label' => ['uk' => 'GitHub'], 'url' => ['uk' => 'https://github.com/roman', 'en' => '']]]])
            ->call('save')
            ->assertHasNoFormErrors();

        $link = ProfileLink::query()->where('user_id', $this->user->id)->sole();
        App::setLocale('en');

        $this->assertSame(['uk' => 'https://github.com/roman'], $link->getTranslations('url'));
        $this->assertSame('https://github.com/roman', $link->url);
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function invalidLinkProvider(): array
    {
        return [
            'url without scheme' => [['label' => ['uk' => 'LinkedIn'], 'url' => ['uk' => 'linkedin.com/in/roman']], 'url.uk', 'url'],
            'ftp scheme' => [['label' => ['uk' => 'Файли'], 'url' => ['uk' => 'ftp://files.example.com']], 'url.uk', 'url'],
            'missing label' => [['label' => ['uk' => ''], 'url' => ['uk' => 'https://roman.dev']], 'label.uk', 'required'],
            'invalid english url' => [['label' => ['uk' => 'LinkedIn'], 'url' => ['uk' => 'https://www.linkedin.com/in/roman', 'en' => 'linkedin.com/in/roman']], 'url.en', 'url'],
            'missing url' => [['label' => ['uk' => 'LinkedIn'], 'url' => ['uk' => '', 'en' => 'https://www.linkedin.com/in/roman']], 'url.uk', 'required'],
            'english label only' => [['label' => ['uk' => '', 'en' => 'Portfolio'], 'url' => ['uk' => 'https://roman.dev']], 'label.uk', 'required'],
        ];
    }

    /**
     * @param array<string, mixed> $invalidLink
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
            ->fillForm(['links' => [['label' => ['uk' => 'Telegram'], 'url' => ['uk' => 'https://t.me/roman']], $invalidLink]])
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
