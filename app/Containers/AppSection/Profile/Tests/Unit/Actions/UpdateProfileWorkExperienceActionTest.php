<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Actions;

use App\Containers\AppSection\Profile\Actions\UpdateProfileWorkExperienceAction;
use App\Containers\AppSection\Profile\Models\ProfileWorkExperience;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UpdateProfileWorkExperienceAction::class)]
final class UpdateProfileWorkExperienceActionTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testUpdatesRoleOfRecord(): void
    {
        $record = ProfileWorkExperience::factory()->createOne(['company_name' => 'MacPaw', 'role' => 'PHP-розробник']);

        $result = app(UpdateProfileWorkExperienceAction::class)->run($record->id, ['role' => ['uk' => 'Senior PHP-розробник']]);

        $this->assertSame($record->id, $result->id);
        $this->assertSame('Senior PHP-розробник', $result->refresh()->role);
        $this->assertSame('MacPaw', $result->company_name);
    }
}
