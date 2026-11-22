<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Pages;

use App\Containers\AppSection\Profile\Enums\LanguageLevel;
use App\Containers\AppSection\Profile\Models\ProfileLanguage;
use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileLanguages;
use App\Containers\AppSection\User\Models\User;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EditProfileLanguages::class)]
final class EditProfileLanguagesTest extends FunctionalTestCase
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
    public function testStoresLanguagesInEnteredOrder(): void
    {
        Livewire::test(EditProfileLanguages::class)
            ->fillForm(['languages' => [
                ['language' => 'Українська', 'level' => 'native'],
                ['language' => 'Англійська', 'level' => 'b2'],
            ]])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Профіль збережено');

        $this->assertSame([['Українська', LanguageLevel::NATIVE], ['Англійська', LanguageLevel::B2]], $this->storedLanguages());
    }

    /**
     * @return void
     */
    public function testChangesLevel(): void
    {
        $this->seedLanguages();

        Livewire::test(EditProfileLanguages::class)
            ->assertSchemaStateSet(['languages.1.level' => LanguageLevel::B2])
            ->fillForm(['languages' => [
                ['language' => 'Українська', 'level' => 'native'],
                ['language' => 'Англійська', 'level' => 'c1'],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([['Українська', LanguageLevel::NATIVE], ['Англійська', LanguageLevel::C1]], $this->storedLanguages());
    }

    /**
     * @return void
     */
    public function testRemovingLanguageKeepsTheOther(): void
    {
        $this->seedLanguages();

        Livewire::test(EditProfileLanguages::class)
            ->fillForm(['languages' => [['language' => 'Українська', 'level' => 'native']]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame([['Українська', LanguageLevel::NATIVE]], $this->storedLanguages());
    }

    /**
     * @return void
     */
    public function testLevelOffersSevenOptionsInOrder(): void
    {
        Livewire::test(EditProfileLanguages::class)
            ->fillForm(['languages' => [['language' => 'Польська']]])
            ->assertFormFieldExists('languages.0.level', static fn (Select $field): bool => [
                'A1 Beginner',
                'A2 Elementary',
                'B1 Intermediate',
                'B2 Upper-Intermediate',
                'C1 Advanced',
                'C2 Proficient',
                'Рідна',
            ] === array_values($field->getOptions()));
    }

    /**
     * @return void
     */
    public function testLanguageWithoutLevelKeepsStoredList(): void
    {
        $this->seedLanguages();

        Livewire::test(EditProfileLanguages::class)
            ->fillForm(['languages' => [['language' => 'Польська', 'level' => null]]])
            ->call('save')
            ->assertHasFormErrors(['languages.0.level' => 'required'])
            ->assertNotNotified('Профіль збережено');

        $this->assertCount(2, $this->storedLanguages());
    }

    /**
     * @return void
     */
    public function testRepeatedLanguageIgnoringCaseAndSpacesKeepsStoredList(): void
    {
        $this->seedLanguages();

        $component = Livewire::test(EditProfileLanguages::class)
            ->fillForm(['languages' => [
                ['language' => 'Англійська', 'level' => 'b2'],
                ['language' => ' англійська ', 'level' => 'c1'],
            ]])
            ->call('save')
            ->assertHasFormErrors(['languages.1.language'])
            ->assertNotNotified('Профіль збережено');

        $this->assertContains('Ця мова вже є в списку.', $component->errors()->all());
        $this->assertSame([['Українська', LanguageLevel::NATIVE], ['Англійська', LanguageLevel::B2]], $this->storedLanguages());
    }

    /**
     * @return void
     */
    public function testAddedDescriptionIsShownAfterReopening(): void
    {
        $this->seedLanguages();
        $description = 'Вільне володіння для роботи з технічною документацією, спілкування з клієнтами та проведення нарад';

        Livewire::test(EditProfileLanguages::class)
            ->fillForm(['languages' => [
                ['language' => 'Українська', 'level' => 'native'],
                ['language' => 'Англійська', 'level' => 'b2', 'description' => $description],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditProfileLanguages::class)
            ->assertSchemaStateSet([
                'languages.0.description' => null,
                'languages.1.language' => 'Англійська',
                'languages.1.description' => $description,
            ]);
    }

    /**
     * @return void
     */
    public function testStoresLanguageWithoutDescription(): void
    {
        Livewire::test(EditProfileLanguages::class)
            ->fillForm(['languages' => [['language' => 'Українська', 'level' => 'native', 'description' => '']]])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Профіль збережено');

        $this->assertSame(['Українська' => null], $this->storedDescriptions());
    }

    /**
     * @return void
     */
    public function testReplacingDescriptionWithSpacesClearsIt(): void
    {
        $this->seedLanguages();
        ProfileLanguage::query()->where('language', 'Англійська')->update(['description' => 'Технічна документація']);

        Livewire::test(EditProfileLanguages::class)
            ->assertSchemaStateSet(['languages.1.description' => 'Технічна документація'])
            ->fillForm(['languages' => [
                ['language' => 'Українська', 'level' => 'native'],
                ['language' => 'Англійська', 'level' => 'b2', 'description' => '   '],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['Українська' => null, 'Англійська' => null], $this->storedDescriptions());
    }

    /**
     * @return void
     */
    public function testDescriptionMovesWithItsLanguage(): void
    {
        $this->seedLanguages();
        ProfileLanguage::query()->where('language', 'Англійська')->update(['description' => 'Технічна документація']);

        Livewire::test(EditProfileLanguages::class)
            ->fillForm(['languages' => [
                ['language' => 'Англійська', 'level' => 'b2', 'description' => 'Технічна документація'],
                ['language' => 'Українська', 'level' => 'native', 'description' => null],
            ]])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditProfileLanguages::class)
            ->assertSchemaStateSet([
                'languages.0.language' => 'Англійська',
                'languages.0.description' => 'Технічна документація',
                'languages.1.language' => 'Українська',
                'languages.1.description' => null,
            ]);
    }

    /**
     * @return void
     */
    public function testTooLongDescriptionKeepsStoredList(): void
    {
        $this->seedLanguages();

        Livewire::test(EditProfileLanguages::class)
            ->fillForm(['languages' => [['language' => 'Англійська', 'level' => 'b2', 'description' => str_repeat('а', 501)]]])
            ->call('save')
            ->assertHasFormErrors(['languages.0.description' => 'max'])
            ->assertNotNotified('Профіль збережено');

        $this->assertSame(['Українська' => null, 'Англійська' => null], $this->storedDescriptions());
    }

    /**
     * @return void
     */
    private function seedLanguages(): void
    {
        ProfileLanguage::factory()->createOne(['user_id' => $this->user->id, 'language' => 'Українська', 'level' => LanguageLevel::NATIVE, 'sort' => 0]);
        ProfileLanguage::factory()->createOne(['user_id' => $this->user->id, 'language' => 'Англійська', 'level' => LanguageLevel::B2, 'sort' => 1]);
    }

    /**
     * @return array<int, array{string, LanguageLevel}>
     */
    private function storedLanguages(): array
    {
        return ProfileLanguage::query()
            ->where('user_id', $this->user->id)
            ->orderBy('sort')
            ->get()
            ->map(static fn (ProfileLanguage $language): array => [$language->language, $language->level])
            ->values()
            ->all();
    }

    /**
     * @return array<string, string|null>
     */
    private function storedDescriptions(): array
    {
        return ProfileLanguage::query()
            ->where('user_id', $this->user->id)
            ->orderBy('sort')
            ->pluck('description', 'language')
            ->all();
    }
}
