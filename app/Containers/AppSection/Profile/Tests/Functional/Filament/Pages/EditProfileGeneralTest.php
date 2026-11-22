<?php

namespace App\Containers\AppSection\Profile\Tests\Functional\Filament\Pages;

use App\Containers\AppSection\Profile\Models\UserProfile;
use App\Containers\AppSection\Profile\Tests\FunctionalTestCase;
use App\Containers\AppSection\Profile\UI\Filament\Pages\EditProfileGeneral;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\MessageBag;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(EditProfileGeneral::class)]
final class EditProfileGeneralTest extends FunctionalTestCase
{
    /**
     * @var User
     */
    private User $user;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->createOne(['email' => 'roman@example.com']);
        $this->actingAs($this->user);
    }

    /**
     * @return void
     */
    public function testFirstSaveCreatesProfileAndNotifies(): void
    {
        Livewire::test(EditProfileGeneral::class)
            ->fillForm([
                'first_name.uk' => 'Роман',
                'last_name.uk' => 'Нерез',
                'headline.uk' => 'Senior FullStack Developer',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Профіль збережено');

        $this->assertDatabaseHas(UserProfile::class, [
            'user_id' => $this->user->id,
            'first_name->uk' => 'Роман',
            'last_name->uk' => 'Нерез',
            'headline->uk' => 'Senior FullStack Developer',
        ]);
    }

    /**
     * @return void
     */
    public function testUkrainianIsActiveByDefault(): void
    {
        UserProfile::factory()->createOne([
            'user_id' => $this->user->id,
            'headline' => ['uk' => 'Senior PHP-розробник', 'en' => 'Senior PHP Developer'],
        ]);

        Livewire::test(EditProfileGeneral::class)
            ->assertSet('dataLocale', 'uk')
            ->assertSchemaStateSet(['headline.uk' => 'Senior PHP-розробник'])
            ->assertSeeHtml("\$wire.dataLocale === 'uk'")
            ->assertSeeHtml("\$wire.dataLocale === 'en'")
            ->assertSeeHtml('English');
    }

    /**
     * @return void
     */
    public function testHeaderSwitcherChangesDataLocale(): void
    {
        Livewire::test(EditProfileGeneral::class)
            ->callAction('dataLocale_en')
            ->assertSet('dataLocale', 'en')
            ->callAction('dataLocale_uk')
            ->assertSet('dataLocale', 'uk');
    }

    /**
     * @return void
     */
    public function testSavesBothLanguagesWhileEnglishIsActive(): void
    {
        UserProfile::factory()->createOne(['user_id' => $this->user->id, 'headline' => ['uk' => 'Розробник', 'en' => 'Developer']]);

        Livewire::test(EditProfileGeneral::class)
            ->fillForm(['headline.uk' => 'Senior PHP-розробник'])
            ->set('dataLocale', 'en')
            ->fillForm(['headline.en' => 'Senior PHP Developer'])
            ->assertSchemaStateSet(['headline.uk' => 'Senior PHP-розробник'])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::test(EditProfileGeneral::class)
            ->assertSchemaStateSet([
                'headline.uk' => 'Senior PHP-розробник',
                'headline.en' => 'Senior PHP Developer',
            ]);
    }

    /**
     * @return void
     */
    public function testEnglishValuesAreOptional(): void
    {
        Livewire::test(EditProfileGeneral::class)
            ->fillForm(['first_name.uk' => 'Роман', 'last_name.uk' => 'Нерез', 'first_name.en' => '', 'headline.uk' => 'Розробник'])
            ->call('save')
            ->assertHasNoFormErrors();

        $profile = UserProfile::query()->where('user_id', $this->user->id)->sole();

        $this->assertSame(['uk' => 'Роман'], $profile->getTranslations('first_name'));
        $this->assertSame(['uk' => 'Розробник'], $profile->getTranslations('headline'));
    }

    /**
     * @return void
     */
    public function testReopeningPageShowsSavedValues(): void
    {
        UserProfile::factory()->createOne([
            'user_id' => $this->user->id,
            'location' => 'Київ, Україна',
            'email' => 'job@example.com',
        ]);

        Livewire::test(EditProfileGeneral::class)
            ->assertSchemaStateSet([
                'location.uk' => 'Київ, Україна',
                'location.en' => null,
                'email' => 'job@example.com',
            ]);
    }

    /**
     * @return void
     */
    public function testLoginEmailIsSuggestedWhileProfileIsMissing(): void
    {
        Livewire::test(EditProfileGeneral::class)
            ->assertSchemaStateSet(['email' => 'roman@example.com', 'first_name.uk' => null]);
    }

    /**
     * @return void
     */
    public function testContactEmailIsStoredSeparatelyFromLoginEmail(): void
    {
        Livewire::test(EditProfileGeneral::class)
            ->fillForm(['first_name.uk' => 'Роман', 'last_name.uk' => 'Нерез', 'email' => 'job@example.com'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(UserProfile::class, ['user_id' => $this->user->id, 'email' => 'job@example.com']);
        $this->assertSame('roman@example.com', $this->user->refresh()->getAttribute('email'));
    }

    /**
     * @return void
     */
    public function testInternationalPhoneIsAccepted(): void
    {
        Livewire::test(EditProfileGeneral::class)
            ->fillForm(['first_name.uk' => 'Роман', 'last_name.uk' => 'Нерез', 'phone' => '+380 (67) 123-45-67'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(UserProfile::class, ['user_id' => $this->user->id, 'phone' => '+380 (67) 123-45-67']);
    }

    /**
     * @return void
     */
    public function testClearingPhoneStoresNull(): void
    {
        UserProfile::factory()->createOne(['user_id' => $this->user->id, 'phone' => '+380 67 123 45 67']);

        Livewire::test(EditProfileGeneral::class)
            ->fillForm(['phone' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(UserProfile::class, ['user_id' => $this->user->id, 'phone' => null]);
    }

    /**
     * @return array<string, array{array<string, mixed>, string, string}>
     */
    public static function invalidDataProvider(): array
    {
        return [
            'empty first name' => [['first_name.uk' => ''], 'first_name.uk', 'required'],
            'incomplete email' => [['email' => 'roman@'], 'email', 'email'],
            'letters in phone' => [['phone' => 'abc123'], 'phone', 'regex'],
            'summary too long' => [['summary.uk' => str_repeat('a', 5001)], 'summary.uk', 'max'],
            'english summary too long' => [['summary.en' => str_repeat('a', 5001)], 'summary.en', 'max'],
            'english first name too long' => [['first_name.en' => str_repeat('a', 101)], 'first_name.en', 'max'],
        ];
    }

    /**
     * @param array<string, mixed> $override
     * @param string $field
     * @param string $rule
     *
     * @return void
     */
    #[DataProvider('invalidDataProvider')]
    public function testInvalidDataIsRejectedWithoutChangingProfile(array $override, string $field, string $rule): void
    {
        $profile = UserProfile::factory()->createOne([
            'user_id' => $this->user->id,
            'first_name' => ['uk' => 'Роман', 'en' => 'Roman'],
            'last_name' => 'Нерез',
        ]);

        Livewire::test(EditProfileGeneral::class)
            ->fillForm([...['first_name.uk' => 'Олег', 'first_name.en' => 'Oleh'], ...$override])
            ->call('save')
            ->assertHasFormErrors([$field => $rule])
            ->assertNotNotified('Профіль збережено');

        $this->assertEquals(['uk' => 'Роман', 'en' => 'Roman'], $profile->refresh()->getTranslations('first_name'));
    }

    /**
     * @return void
     */
    public function testEmptyFirstNameShowsUkrainianMessage(): void
    {
        $component = Livewire::test(EditProfileGeneral::class)
            ->fillForm(['first_name.uk' => '', 'last_name.uk' => 'Нерез'])
            ->call('save');

        /** @var MessageBag $errors */
        $errors = $component->errors();

        $this->assertContains("Поле ім'я є обов’язковим.", $errors->all());
    }

    /**
     * @return void
     */
    public function testUkrainianErrorWhileEnglishIsActiveSwitchesToUkrainian(): void
    {
        UserProfile::factory()->createOne(['user_id' => $this->user->id, 'first_name' => 'Роман']);

        $component = Livewire::test(EditProfileGeneral::class)
            ->set('dataLocale', 'en')
            ->fillForm(['first_name.uk' => ''])
            ->call('save')
            ->assertHasFormErrors(['first_name.uk' => 'required'])
            ->assertSet('dataLocale', 'uk');

        /** @var MessageBag $errors */
        $errors = $component->errors();

        $this->assertContains("Поле ім'я є обов’язковим.", $errors->all());
    }

    /**
     * @return void
     */
    public function testEnglishErrorWhileUkrainianIsActiveSwitchesToEnglish(): void
    {
        Livewire::test(EditProfileGeneral::class)
            ->fillForm(['first_name.uk' => 'Роман', 'last_name.uk' => 'Нерез', 'headline.en' => str_repeat('a', 256)])
            ->call('save')
            ->assertHasFormErrors(['headline.en' => 'max'])
            ->assertSet('dataLocale', 'en');
    }

    /**
     * @return void
     */
    public function testEachUserSeesOwnProfile(): void
    {
        UserProfile::factory()->createOne(['first_name' => 'Роман']);
        UserProfile::factory()->createOne(['user_id' => $this->user->id, 'first_name' => 'Олена']);

        Livewire::test(EditProfileGeneral::class)
            ->assertSchemaStateSet(['first_name.uk' => 'Олена']);
    }
}
