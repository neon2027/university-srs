<?php

namespace App\Livewire\Public;

use App\Models\Ticket;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.public')]
class TicketTracker extends Component
{
    use WithFileUploads;

    public string $ticketNumber = '';

    public string $lastName = '';

    public string $lookupError = '';

    public string $replyBody = '';

    public array $attachmentFiles = [];

    public function lookup(): void
    {
        $this->validate([
            'ticketNumber' => ['required', 'string'],
            'lastName' => ['required', 'string'],
        ]);

        $key = 'ticket-tracker:'.request()->ip();

        $executed = RateLimiter::attempt($key, 10, function (): void {
            $ticket = Ticket::query()
                ->where('ulid', trim($this->ticketNumber))
                ->with('requester')
                ->first();

            if (! $ticket || ! $this->lastNameMatches($ticket)) {
                $this->lookupError = 'Ticket not found or details do not match.';

                return;
            }

            session(['tracker.ticket_id' => $ticket->id]);

            $this->lookupError = '';
            $this->ticketNumber = '';
            $this->lastName = '';
        });

        if (! $executed) {
            $this->lookupError = 'Too many attempts. Please try again later.';
        }
    }

    public function sendReply(): void
    {
        $ticket = $this->verifiedTicket();

        $this->replyBody = trim($this->replyBody);

        $this->validate([
            'replyBody' => ['required', 'string', 'max:5000'],
        ]);

        $ticket->messages()->create([
            'sender_id' => null,
            'guest_name' => $ticket->requester->name,
            'body' => $this->replyBody,
            'is_internal_note' => false,
            'is_canned_response' => false,
            'requests_attachment' => false,
        ]);

        $this->replyBody = '';
    }

    public function uploadAttachment(int $messageId): void
    {
        $ticket = $this->verifiedTicket();

        $message = $ticket->messages()
            ->whereKey($messageId)
            ->where('is_internal_note', false)
            ->where('requests_attachment', true)
            ->firstOrFail();

        $this->validate([
            "attachmentFiles.{$message->id}" => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xlsx,csv', 'max:10240'],
        ]);

        $file = $this->attachmentFiles[$message->id];
        $path = $file->store('ticket-attachments', 'local');

        $ticket->attachments()->create([
            'ticket_message_id' => $message->id,
            'uploader_id' => null,
            'disk' => 'local',
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        unset($this->attachmentFiles[$message->id]);
    }

    public function clearSession(): void
    {
        session()->forget('tracker.ticket_id');
    }

    public function render(): View
    {
        if (! session()->has('tracker.ticket_id')) {
            return view('livewire.public.ticket-tracker', [
                'isVerified' => false,
                'ticket' => null,
                'messages' => collect(),
            ]);
        }

        $ticket = $this->verifiedTicket()->load(['office', 'serviceType', 'requester']);

        $messages = $ticket->messages()
            ->where('is_internal_note', false)
            ->with('sender')
            ->oldest()
            ->get();

        return view('livewire.public.ticket-tracker', [
            'isVerified' => true,
            'ticket' => $ticket,
            'messages' => $messages,
        ]);
    }

    private function verifiedTicket(): Ticket
    {
        return Ticket::query()
            ->with('requester')
            ->findOrFail(session('tracker.ticket_id'));
    }

    private function lastNameMatches(Ticket $ticket): bool
    {
        $given = str($this->lastName)->trim()->lower()->squish()->toString();
        $requesterName = str($ticket->requester->name)->trim()->lower()->squish()->toString();
        $requesterLastWord = str($requesterName)->afterLast(' ')->toString();

        return $given === $requesterLastWord || str($requesterName)->endsWith($given);
    }
}
