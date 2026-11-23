<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Actions;

use App\Containers\AppSection\Profile\Actions\CreateProfileWorkExperienceAction;
use App\Containers\AppSection\Profile\Enums\WorkExperienceType;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreateProfileWorkExperienceAction::class)]
final class CreateProfileWorkExperienceActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesCurrentExperienceForGivenUser(): void
    {
        $user = User::factory()->createOne();

        $record = app(CreateProfileWorkExperienceAction::class)->run($user->id, [
            'type' => WorkExperienceType::COMPANY->value,
            'company_name' => ['uk' => 'Grammarly'],
            'role' => ['uk' => 'Backend Engineer'],
            'started_on' => '2024-09-01',
            'finished_on' => null,
        ]);

        $this->assertDatabaseHas(ProfileWorkExperience::class, [
            'id' => $record->id,
            'user_id' => $user->id,
            'type' => 'company',
            'finished_on' => null,
        ]);
    }
}
