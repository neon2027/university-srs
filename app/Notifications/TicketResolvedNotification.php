<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketResolvedNotification extends Notification
{
    public function __construct(public Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your Ticket Has Been Resolved – {$this->ticket->subject}")
            ->greeting("Hello, {$notifiable->name}!")
            ->line('We are pleased to inform you that your support request has been resolved.')
            ->line("**Subject:** {$this->ticket->subject}")
            ->line("**Office:** {$this->ticket->office->name}")
            ->line('We value your feedback. Please take a moment to rate your experience with our service.')
            ->action('Rate Your Experience', route('portal.tickets.show', $this->ticket->ulid))
            ->line('If you feel your concern has not been fully addressed, you may reopen the ticket through the portal.');
    }
}
