<?php

namespace App\Livewire\Admin;

use App\Models\CannedResponse;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;

class TicketMessaging extends Component
{
    public Ticket $ticket;

    public string $body = '';

    public bool $isInternalNote = false;

    public bool $requestsAttachment = false;

    public ?string $errorMessage = null;

    public function applyCannedResponse(string $body): void
    {
        $this->body = $body;
        $this->errorMessage = null;
    }

    public function send(): void
    {
        $user = auth()->user();

        if (! $this->canReply($user)) {
            $this->errorMessage = 'You can only reply to tickets assigned to you or tickets from your office.';

            return;
        }

        if ($this->isInternalNote && ! $user->hasAnyRole(['staff', 'office_admin', 'super_admin'])) {
            $this->isInternalNote = false;
        }

        if ($this->isInternalNote) {
            $this->requestsAttachment = false;
        }

        $this->body = trim($this->body);

        $this->validate([
            'body' => 'required|string|max:5000',
        ]);

        TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'sender_id' => auth()->id(),
            'body' => $this->body,
            'is_internal_note' => $this->isInternalNote,
            'is_canned_response' => false,
            'requests_attachment' => $this->requestsAttachment,
        ]);

        $this->body = '';
        $this->isInternalNote = false;
        $this->requestsAttachment = false;
        $this->errorMessage = null;
    }

    public function render(): View
    {
        $messages = $this->loadMessages();
        $cannedResponses = $this->loadCannedResponses();

        return view('livewire.admin.ticket-messaging', compact('messages', 'cannedResponses'));
    }

    private function loadMessages(): Collection
    {
        return TicketMessage::with('sender')
            ->where('ticket_id', $this->ticket->id)
            ->where(function ($q) {
                if (! auth()->user()->hasAnyRole(['staff', 'office_admin', 'super_admin'])) {
                    $q->where('is_internal_note', false);
                }
            })
            ->orderBy('created_at')
            ->get();
    }

    private function loadCannedResponses(): Collection
    {
        return CannedResponse::active()
            ->forOffice($this->ticket->office_id ?? 0)
            ->orderBy('title')
            ->get();
    }

    private function canReply(User $user): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($this->ticket->assigned_to === $user->id) {
            return true;
        }

        if (! $user->hasAnyRole(['staff', 'office_admin'])) {
            return false;
        }

        return $user->offices()
            ->whereKey($this->ticket->office_id)
            ->exists();
    }
}
