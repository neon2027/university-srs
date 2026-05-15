<?php

namespace App\Notifications;

use App\Models\Office;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeVerificationRequestedNotification extends Notification
{
    public function __construct(
        public User $requester,
        public Office $office,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Employee verification request')
            ->greeting('New employee verification request')
            ->line("{$this->requester->name} requested to join {$this->office->name}.")
            ->line("Email: {$this->requester->email}")
            ->action('Review request', url('/admin/users'))
            ->line('Please review the user and assign the appropriate staff role if the request is valid.');
    }
}
