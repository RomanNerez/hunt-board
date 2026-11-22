<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Models;

use App\Containers\AppSection\Profile\Models\ProfileEducation;
use App\Containers\AppSection\Profile\Models\ProfileLanguage;
use App\Containers\AppSection\Profile\Models\ProfileLink;
use App\Containers\AppSection\Profile\Models\ProfileSkillGroup;
use App\Containers\AppSection\Profile\Models\UserProfile;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserProfile::class)]
final class UserProfileTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testListsBelongToProfileUserInSortOrder(): void
    {
        $profile = UserProfile::factory()->createOne();
        ProfileLink::factory()->createOne(['user_id' => $profile->user_id, 'label' => 'GitHub', 'sort' => 1]);
        ProfileLink::factory()->createOne(['user_id' => $profile->user_id, 'label' => 'LinkedIn', 'sort' => 0]);
        ProfileLink::factory()->createOne(['label' => 'Чуже']);
        ProfileEducation::factory()->createOne(['user_id' => $profile->user_id]);
        ProfileLanguage::factory()->createOne(['user_id' => $profile->user_id]);
        ProfileSkillGroup::factory()->createOne(['user_id' => $profile->user_id, 'name' => 'Frontend', 'sort' => 1]);
        ProfileSkillGroup::factory()->createOne(['user_id' => $profile->user_id, 'name' => 'Backend', 'sort' => 0]);
        ProfileSkillGroup::factory()->createOne(['name' => 'Чуже']);

        $this->assertSame(['LinkedIn', 'GitHub'], $profile->links()->pluck('label')->all());
        $this->assertSame(['Backend', 'Frontend'], $profile->skillGroups()->pluck('name')->all());
        $this->assertSame(1, $profile->educations()->count());
        $this->assertSame(1, $profile->languages()->count());
    }

    /**
     * @return void
     */
    public function testDeletingUserRemovesWholeProfile(): void
    {
        $user = User::factory()->createOne();
        $other = UserProfile::factory()->createOne();
        UserProfile::factory()->createOne(['user_id' => $user->id]);
        ProfileLink::factory()->createOne(['user_id' => $user->id]);
        ProfileEducation::factory()->createOne(['user_id' => $user->id]);
        ProfileLanguage::factory()->createOne(['user_id' => $user->id]);
        ProfileSkillGroup::factory()->createOne(['user_id' => $user->id]);

        $user->delete();

        foreach ([UserProfile::class, ProfileLink::class, ProfileEducation::class, ProfileLanguage::class, ProfileSkillGroup::class] as $model) {
            $this->assertDatabaseMissing($model, ['user_id' => $user->id]);
        }

        $this->assertModelExists($other);
    }

    /**
     * @return void
     */
    public function testKeepsUkrainianAndEnglishValuesSeparately(): void
    {
        $profile = UserProfile::factory()->createOne([
            'headline' => ['uk' => 'Senior PHP-розробник', 'en' => 'Senior PHP Developer'],
        ]);

        $this->assertEquals(
            ['uk' => 'Senior PHP-розробник', 'en' => 'Senior PHP Developer'],
            $profile->refresh()->getTranslations('headline'),
        );
        $this->assertSame('Senior PHP-розробник', $profile->headline);
    }

    /**
     * @return void
     */
    public function testEnglishLocaleFallsBackToUkrainianValue(): void
    {
        $profile = UserProfile::factory()->createOne(['headline' => ['uk' => 'Senior PHP-розробник']]);

        App::setLocale('en');

        $this->assertSame('Senior PHP-розробник', $profile->refresh()->headline);
    }

    /**
     * @return void
     */
    public function testEmptyOptionalFieldIsStoredAsNull(): void
    {
        $profile = UserProfile::factory()->createOne(['headline' => null]);

        $this->assertDatabaseHas(UserProfile::class, ['id' => $profile->id, 'headline' => null]);
        $this->assertNull($profile->refresh()->headline);
    }
}
