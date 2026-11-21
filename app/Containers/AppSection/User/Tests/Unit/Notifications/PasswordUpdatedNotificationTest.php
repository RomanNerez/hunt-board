<?php

namespace App\Containers\AppSection\User\Tests\Unit\Notifications;

use App\Containers\AppSection\User\Models\User;
use App\Containers\AppSection\User\Notifications\PasswordUpdatedNotification;
use App\Containers\AppSection\User\Tests\UnitTestCase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PasswordUpdatedNotification::class)]
final class PasswordUpdatedNotificationTest extends UnitTestCase
{
    public function testCanSendMail(): void
    {
        Notification::fake();
        Notification::assertNothingSent();
        $user = User::factory()->createOne();

        $user->notify(new PasswordUpdatedNotification());

        Notification::assertSentTo($user, PasswordUpdatedNotification::class, function (PasswordUpdatedNotification $notification) use ($user) {
            $email = $notification->toMail($user);
            $this->assertSame('Зміни в обліковому записі', $email->subject);
            $this->assertSame([
                'Повідомляємо, що в вашому обліковому записі змінено дані:',
                'Ваш пароль змінено.',
                'Якщо це зробили ви, просто проігноруйте цей лист. Якщо ж ви НЕ змінювали обліковий запис, якнайшвидше змініть пароль і виправте дані, щоб захистити свій обліковий запис.',
            ], $email->introLines);

            return true;
        });
        Notification::assertCount(1);
    }
}
