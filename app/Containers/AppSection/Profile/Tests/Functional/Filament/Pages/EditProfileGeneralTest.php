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
                'first_name' => 'Роман',
                'last_name' => 'Нерез',
                'headline' => 'Senior FullStack Developer',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Профіль збережено');

        $this->assertDatabaseHas(UserProfile::class, [
            'user_id' => $this->user->id,
            'first_name' => 'Роман',
            'last_name' => 'Нерез',
            'headline' => 'Senior FullStack Developer',
        ]);
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
                'location' => 'Київ, Україна',
                'email' => 'job@example.com',
            ]);
    }

    /**
     * @return void
     */
    public function testLoginEmailIsSuggestedWhileProfileIsMissing(): void
    {
        Livewire::test(EditProfileGeneral::class)
            ->assertSchemaStateSet(['email' => 'roman@example.com', 'first_name' => null]);
    }

    /**
     * @return void
     */
    public function testContactEmailIsStoredSeparatelyFromLoginEmail(): void
    {
        Livewire::test(EditProfileGeneral::class)
            ->fillForm(['first_name' => 'Роман', 'last_name' => 'Нерез', 'email' => 'job@example.com'])
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
            ->fillForm(['first_name' => 'Роман', 'last_name' => 'Нерез', 'phone' => '+380 (67) 123-45-67'])
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
            'empty first name' => [['first_name' => ''], 'first_name', 'required'],
            'incomplete email' => [['email' => 'roman@'], 'email', 'email'],
            'letters in phone' => [['phone' => 'abc123'], 'phone', 'regex'],
            'summary too long' => [['summary' => str_repeat('a', 5001)], 'summary', 'max'],
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
        $profile = UserProfile::factory()->createOne(['user_id' => $this->user->id, 'first_name' => 'Роман', 'last_name' => 'Нерез']);

        Livewire::test(EditProfileGeneral::class)
            ->fillForm([...['first_name' => 'Олег', 'last_name' => 'Нерез'], ...$override])
            ->call('save')
            ->assertHasFormErrors([$field => $rule])
            ->assertNotNotified('Профіль збережено');

        $this->assertSame('Роман', $profile->refresh()->first_name);
    }

    /**
     * @return void
     */
    public function testEmptyFirstNameShowsUkrainianMessage(): void
    {
        $component = Livewire::test(EditProfileGeneral::class)
            ->fillForm(['first_name' => '', 'last_name' => 'Нерез'])
            ->call('save');

        /** @var MessageBag $errors */
        $errors = $component->errors();

        $this->assertContains("Поле ім'я є обов’язковим.", $errors->all());
    }

    /**
     * @return void
     */
    public function testEachUserSeesOwnProfile(): void
    {
        UserProfile::factory()->createOne(['first_name' => 'Роман']);
        UserProfile::factory()->createOne(['user_id' => $this->user->id, 'first_name' => 'Олена']);

        Livewire::test(EditProfileGeneral::class)
            ->assertSchemaStateSet(['first_name' => 'Олена']);
    }
}
