<?php

namespace App\Ship\Tests\Unit\Parents\Models;

use App\Ship\Parents\Models\UserModel;
use App\Ship\Tests\Fakes\TestUser;
use App\Ship\Tests\ShipTestCase;
use Illuminate\Notifications\Notifiable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserModel::class)]
final class UserModelTest extends ShipTestCase
{
    public function testClassUsesCorrectTraits(): void
    {
        $this->assertContains(Notifiable::class, class_uses_recursive(TestUser::class));
    }
}
