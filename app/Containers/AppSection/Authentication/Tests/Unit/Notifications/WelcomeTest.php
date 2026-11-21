<?php

namespace App\Containers\AppSection\Authentication\Tests\Unit\Notifications;

use App\Containers\AppSection\Authentication\Notifications\Welcome;
use App\Containers\AppSection\Authentication\Tests\UnitTestCase;
use App\Containers\AppSection\User\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Welcome::class)]
final class WelcomeTest extends UnitTestCase
{
    public function testSendMail(): void
    {
        $notification = new Welcome();
        $user = User::factory()->make();

        $result = $notification->toMail($user);

        $this->assertSame('Ласкаво просимо до ' . config('app.name'), $result->subject);
        $this->assertSame(['Дякуємо за реєстрацію, ' . $user->name], $result->introLines);
    }
}
