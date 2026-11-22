<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Actions;

use App\Containers\AppSection\Profile\Actions\UpdateUserProfileAction;
use App\Containers\AppSection\Profile\Models\UserProfile;
use App\Containers\AppSection\Profile\Tasks\UpsertUserProfileTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateUserProfileAction::class)]
#[CoversClass(UpsertUserProfileTask::class)]
final class UpdateUserProfileActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testFirstCallCreatesProfileAndSecondUpdatesSameRow(): void
    {
        $user = User::factory()->createOne();
        $action = app(UpdateUserProfileAction::class);

        $created = $action->run($user->id, ['first_name' => 'Роман', 'last_name' => 'Нерез', 'headline' => 'Senior FullStack Developer']);
        $updated = $action->run($user->id, ['first_name' => 'Роман', 'last_name' => 'Нерез', 'location' => 'Київ, Україна']);

        $this->assertSame($created->id, $updated->id);
        $this->assertSame(1, UserProfile::query()->where('user_id', $user->id)->count());
        $this->assertDatabaseHas(UserProfile::class, [
            'id' => $created->id,
            'user_id' => $user->id,
            'headline->uk' => 'Senior FullStack Developer',
            'location->uk' => 'Київ, Україна',
        ]);
    }

    /**
     * @return void
     */
    public function testClearingPhoneStoresNull(): void
    {
        $profile = UserProfile::factory()->createOne(['phone' => '+380 67 123 45 67']);

        app(UpdateUserProfileAction::class)->run($profile->user_id, [
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'phone' => null,
        ]);

        $this->assertNull($profile->refresh()->phone);
    }

    /**
     * @return void
     */
    public function testDoesNotTouchAnotherUsersProfile(): void
    {
        $other = UserProfile::factory()->createOne(['first_name' => 'Олена']);
        $user = User::factory()->createOne();

        app(UpdateUserProfileAction::class)->run($user->id, [
            'first_name' => 'Роман',
            'last_name' => 'Нерез',
            'user_id' => $other->user_id,
        ]);

        $this->assertSame('Олена', $other->refresh()->first_name);
        $this->assertDatabaseHas(UserProfile::class, ['user_id' => $user->id, 'first_name->uk' => 'Роман']);
    }

    /**
     * @return void
     */
    public function testStoresTrimmedUkrainianAndEnglishValues(): void
    {
        $user = User::factory()->createOne();

        $profile = app(UpdateUserProfileAction::class)->run($user->id, [
            'first_name' => ['uk' => ' Роман ', 'en' => ' Roman '],
            'last_name' => ['uk' => 'Нерез', 'en' => 'Nerez'],
            'headline' => ['uk' => 'Senior PHP-розробник', 'en' => 'Senior PHP Developer', 'de' => 'Entwickler'],
        ]);

        $this->assertEquals(['uk' => 'Роман', 'en' => 'Roman'], $profile->refresh()->getTranslations('first_name'));
        $this->assertEquals(['uk' => 'Senior PHP-розробник', 'en' => 'Senior PHP Developer'], $profile->getTranslations('headline'));
    }

    /**
     * @return void
     */
    public function testBlankEnglishValueIsDroppedAndReplacesStoredOne(): void
    {
        $profile = UserProfile::factory()->createOne(['headline' => ['uk' => 'Розробник', 'en' => 'Developer']]);

        app(UpdateUserProfileAction::class)->run($profile->user_id, [
            'first_name' => ['uk' => 'Роман', 'en' => ''],
            'last_name' => ['uk' => 'Нерез'],
            'headline' => ['uk' => 'Senior PHP-розробник', 'en' => '   '],
        ]);

        $profile->refresh();

        $this->assertSame(['uk' => 'Роман'], $profile->getTranslations('first_name'));
        $this->assertSame(['uk' => 'Senior PHP-розробник'], $profile->getTranslations('headline'));
    }

    /**
     * @return void
     */
    public function testOptionalFieldWithoutAnyLanguageIsStoredAsNull(): void
    {
        $profile = UserProfile::factory()->createOne(['summary' => ['uk' => 'Про мене', 'en' => 'About me']]);

        app(UpdateUserProfileAction::class)->run($profile->user_id, [
            'first_name' => ['uk' => 'Роман'],
            'last_name' => ['uk' => 'Нерез'],
            'summary' => ['uk' => ' ', 'en' => ''],
            'location' => null,
        ]);

        $this->assertDatabaseHas(UserProfile::class, ['id' => $profile->id, 'summary' => null, 'location' => null]);
    }
}
