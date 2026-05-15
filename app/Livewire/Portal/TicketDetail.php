<?php

namespace App\Livewire\Portal;

use App\Models\Ticket;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class TicketDetail extends Component
{
    #[Locked]
    public string $ulid = '';

    public string $messageBody = '';

    public function mount(string $ulid): void
    {
        $this->ulid = $ulid;

        $ticket = $this->findTicket();

        $ticket->messages()
            ->where('sender_id', '!=', auth()->id())
            ->where('is_internal_note', false)
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);
    }

    public function sendMessage(): void
    {
        $this->validate(['messageBody' => 'required|string|max:5000']);

        $this->findTicket()->messages()->create([
            'sender_id' => auth()->id(),
            'body' => $this->messageBody,
            'is_internal_note' => false,
            'is_canned_response' => false,
        ]);

        $this->messageBody = '';
    }

    public function render(): View
    {
        $ticket = $this->findTicket()->load(['office', 'serviceType', 'history.actor']);
        $messages = $ticket->messages()
            ->where('is_internal_note', false)
            ->with('sender')
            ->oldest()
            ->get();
        $recentTickets = Ticket::with(['office', 'serviceType'])
            ->where('requester_id', auth()->id())
            ->latest()
            ->limit(12)
            ->get();

        return view('livewire.portal.ticket-detail', compact('ticket', 'messages', 'recentTickets'));
    }

    private function findTicket(): Ticket
    {
        return Ticket::where('ulid', $this->ulid)
            ->where('requester_id', auth()->id())
            ->firstOrFail();
    }
}
