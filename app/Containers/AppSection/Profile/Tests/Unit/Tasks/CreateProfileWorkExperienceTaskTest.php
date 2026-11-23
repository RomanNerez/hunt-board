<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Tasks;

use App\Containers\AppSection\Profile\Enums\WorkExperienceType;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tasks\CreateProfileWorkExperienceTask;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateProfileWorkExperienceTask::class)]
final class CreateProfileWorkExperienceTaskTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testStoresBothLanguagesForGivenUser(): void
    {
        $user = User::factory()->createOne();

        $record = app(CreateProfileWorkExperienceTask::class)->run($user->id, [
            'type' => WorkExperienceType::COMPANY->value,
            'company_name' => ['uk' => ' MacPaw ', 'en' => 'MacPaw'],
            'role' => ['uk' => 'Senior PHP-розробник', 'en' => 'Senior PHP Developer'],
            'started_on' => '2021-03-01',
            'finished_on' => '2024-08-31',
        ])->refresh();

        $this->assertSame($user->id, $record->user_id);
        $this->assertSame(WorkExperienceType::COMPANY, $record->type);
        $this->assertEquals(['uk' => 'MacPaw', 'en' => 'MacPaw'], $record->getTranslations('company_name'));
        $this->assertEquals(['uk' => 'Senior PHP-розробник', 'en' => 'Senior PHP Developer'], $record->getTranslations('role'));
        $this->assertSame('2021-03-01', $record->started_on->toDateString());
        $this->assertSame('2024-08-31', $record->finished_on?->toDateString());
    }

    /**
     * @return void
     */
    public function testBlankEnglishFallsBackToUkrainian(): void
    {
        $user = User::factory()->createOne();

        $record = app(CreateProfileWorkExperienceTask::class)->run($user->id, [
            'type' => WorkExperienceType::COMPANY->value,
            'company_name' => ['uk' => 'Grammarly', 'en' => ''],
            'role' => ['uk' => 'PHP-розробник', 'en' => '  '],
            'started_on' => '2024-09-01',
        ])->refresh();

        $this->assertSame(['uk' => 'PHP-розробник'], $record->getTranslations('role'));
        $this->assertNull($record->finished_on);

        App::setLocale('en');

        $this->assertSame('PHP-розробник', $record->role);
    }

    /**
     * @return void
     */
    public function testIgnoresOwnerAndUnknownAttributesFromData(): void
    {
        $user = User::factory()->createOne();
        $other = User::factory()->createOne();

        $record = app(CreateProfileWorkExperienceTask::class)->run($user->id, [
            'user_id' => $other->id,
            'id' => 999,
            'type' => WorkExperienceType::FREELANCE->value,
            'company_name' => ['uk' => 'Інтернет-магазин для Kavapro'],
            'role' => ['uk' => 'Full-stack розробник'],
            'started_on' => '2020-01-01',
        ]);

        $this->assertSame($user->id, $record->user_id);
        $this->assertNotSame(999, $record->id);
        $this->assertDatabaseMissing(ProfileWorkExperience::class, ['user_id' => $other->id]);
    }
}
