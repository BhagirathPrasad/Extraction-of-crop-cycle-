<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserLoginNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public User $user) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'user_login',
            'title'      => 'User Login',
            'message'    => "New user {$this->user->name} logged in successfully",
            'username'   => $this->user->name,
            'role'       => $this->user->role,
            'login_time' => now()->toIso8601String(),
            'icon'       => 'box-arrow-in-right',
            'color'      => 'info',
        ];
    }
}
