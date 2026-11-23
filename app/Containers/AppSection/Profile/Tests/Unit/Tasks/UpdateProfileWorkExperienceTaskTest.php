<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Tasks;

use App\Containers\AppSection\Profile\Enums\WorkExperienceType;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tasks\UpdateProfileWorkExperienceTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateProfileWorkExperienceTask::class)]
final class UpdateProfileWorkExperienceTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUpdatesTranslationsAndClearsFinishDate(): void
    {
        $record = ProfileWorkExperience::factory()->createOne(['role' => ['uk' => 'PHP-розробник']]);

        $result = app(UpdateProfileWorkExperienceTask::class)->run($record->id, [
            'type' => WorkExperienceType::FREELANCE->value,
            'role' => ['uk' => 'Senior PHP-розробник', 'en' => 'Senior PHP Developer'],
            'finished_on' => null,
        ])->refresh();

        $this->assertSame($record->id, $result->id);
        $this->assertSame(WorkExperienceType::FREELANCE, $result->type);
        $this->assertEquals(['uk' => 'Senior PHP-розробник', 'en' => 'Senior PHP Developer'], $result->getTranslations('role'));
        $this->assertNull($result->finished_on);
    }

    /**
     * @return void
     */
    public function testBlankEnglishIsRemoved(): void
    {
        $record = ProfileWorkExperience::factory()->createOne(['role' => ['uk' => 'PHP-розробник', 'en' => 'PHP Developer']]);

        $result = app(UpdateProfileWorkExperienceTask::class)->run($record->id, [
            'role' => ['uk' => 'PHP-розробник', 'en' => ''],
        ])->refresh();

        $this->assertSame(['uk' => 'PHP-розробник'], $result->getTranslations('role'));
    }

    /**
     * @return void
     */
    public function testIgnoresOwnerChangeInData(): void
    {
        $record = ProfileWorkExperience::factory()->createOne();
        $other = User::factory()->createOne();

        app(UpdateProfileWorkExperienceTask::class)->run($record->id, [
            'user_id' => $other->id,
            'company_name' => ['uk' => 'MacPaw'],
        ]);

        $this->assertDatabaseHas(ProfileWorkExperience::class, ['id' => $record->id, 'user_id' => $record->user_id]);
        $this->assertDatabaseMissing(ProfileWorkExperience::class, ['user_id' => $other->id]);
    }
}
