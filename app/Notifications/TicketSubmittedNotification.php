<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketSubmittedNotification extends Notification
{
    public function __construct(public Ticket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Ticket #{$this->ticket->ulid} Submitted – {$this->ticket->subject}")
            ->greeting("Hello, {$notifiable->name}!")
            ->line('Your request has been successfully submitted and is now being reviewed by our team.')
            ->line("**Subject:** {$this->ticket->subject}")
            ->line("**Office:** {$this->ticket->office->name}")
            ->line('**Status:** Pending')
            ->action('View Ticket', route('portal.tickets.show', $this->ticket->ulid))
            ->line('You will receive an email once a staff member responds to your request.');
    }
}
