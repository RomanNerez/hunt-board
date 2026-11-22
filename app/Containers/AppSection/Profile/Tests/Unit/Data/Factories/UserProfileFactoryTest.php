<?php

namespace App\Containers\AppSection\Profile\Tests\Unit\Data\Factories;

use App\Containers\AppSection\Profile\Data\Factories\UserProfileFactory;
use App\Containers\AppSection\Profile\Models\UserProfile;
use App\Containers\AppSection\Profile\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserProfileFactory::class)]
final class UserProfileFactoryTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testCreatesProfileForNewUser(): void
    {
        $record = UserProfile::factory()->createOne()->refresh();

        $this->assertNotSame('', $record->first_name);
        $this->assertNotSame('', $record->last_name);
        $this->assertSame($record->user_id, $record->user()->firstOrFail()->id);
    }
}
