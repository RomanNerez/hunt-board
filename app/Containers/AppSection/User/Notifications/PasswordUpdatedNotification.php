<?php

namespace App\Containers\AppSection\User\Notifications;

use App\Ship\Parents\Models\UserModel;
use App\Ship\Parents\Notifications\Notification as ParentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

final class PasswordUpdatedNotification extends ParentNotification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail(UserModel $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Зміни в обліковому записі')
            ->line('Повідомляємо, що в вашому обліковому записі змінено дані:')
            ->line('Ваш пароль змінено.')
            ->line('Якщо це зробили ви, просто проігноруйте цей лист. Якщо ж ви НЕ змінювали обліковий запис, якнайшвидше змініть пароль і виправте дані, щоб захистити свій обліковий запис.');
    }
}
