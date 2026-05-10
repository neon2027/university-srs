<?php

namespace App\Livewire\Admin;

use App\Models\CannedResponse;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;

class TicketMessaging extends Component
{
    public Ticket $ticket;

    public string $body = '';

    public bool $isInternalNote = false;

    public function applyCannedResponse(string $body): void
    {
        $this->body = $body;
    }

    public function send(): void
    {
        $user = auth()->user();

        if (! $user->hasRole('super_admin') && $user->hasAnyRole(['staff', 'office_admin'])) {
            $officeIds = $user->offices()->pluck('offices.id');
            if (! $officeIds->contains($this->ticket->office_id)) {
                return;
            }
        }

        if ($this->isInternalNote && ! $user->hasAnyRole(['staff', 'office_admin', 'super_admin'])) {
            $this->isInternalNote = false;
        }

        $this->validate([
            'body' => 'required|string|max:5000',
        ]);

        TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'sender_id' => auth()->id(),
            'body' => $this->body,
            'is_internal_note' => $this->isInternalNote,
            'is_canned_response' => false,
        ]);

        $this->body = '';
        $this->isInternalNote = false;
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
}
