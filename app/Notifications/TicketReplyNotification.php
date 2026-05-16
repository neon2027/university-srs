<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketReplyNotification extends Notification
{
    public function __construct(
        public Ticket $ticket,
        public TicketMessage $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $senderName = $this->message->sender->name ?? 'Support Staff';

        return (new MailMessage)
            ->subject("New Reply on Ticket #{$this->ticket->ulid} – {$this->ticket->subject}")
            ->greeting("Hello, {$notifiable->name}!")
            ->line("{$senderName} has replied to your support request.")
            ->line('**Message:**')
            ->line($this->message->body)
            ->action('View & Reply', route('portal.tickets.show', $this->ticket->ulid))
            ->line('Log in to the portal to view the full conversation and send a reply.');
    }
}
