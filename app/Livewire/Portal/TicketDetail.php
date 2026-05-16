<?php

namespace App\Livewire\Portal;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class TicketDetail extends Component
{
    #[Locked]
    public string $ulid = '';

    public string $messageBody = '';

    #[Rule('required|integer|min:1|max:5')]
    public int $overallRating = 0;

    #[Rule('required|integer|min:1|max:5')]
    public int $serviceRating = 0;

    #[Rule('nullable|integer|min:1|max:5')]
    public ?int $staffRating = null;

    #[Rule('nullable|string|max:1000')]
    public string $ratingComment = '';

    public bool $ratingSubmitted = false;

    public function mount(string $ulid): void
    {
        $this->ulid = $ulid;

        $ticket = $this->findTicket();

        $ticket->messages()
            ->where('sender_id', '!=', auth()->id())
            ->where('is_internal_note', false)
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        $this->ratingSubmitted = $ticket->rating()->exists();
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
        $this->dispatch('message-sent');
    }

    public function submitRating(): void
    {
        $ticket = $this->findTicket();

        if ($ticket->rating()->exists()) {
            return;
        }

        if (! in_array($ticket->status, [TicketStatus::Resolved, TicketStatus::Closed])) {
            return;
        }

        $validated = $this->validate([
            'overallRating' => 'required|integer|min:1|max:5',
            'serviceRating' => 'required|integer|min:1|max:5',
            'staffRating' => 'nullable|integer|min:1|max:5',
            'ratingComment' => 'nullable|string|max:1000',
        ]);

        $ticket->rating()->create([
            'rater_id' => auth()->id(),
            'overall_rating' => $validated['overallRating'],
            'service_rating' => $validated['serviceRating'],
            'staff_rating' => $ticket->assigned_to ? $validated['staffRating'] : null,
            'comment' => $validated['ratingComment'] ?: null,
        ]);

        $this->ratingSubmitted = true;
    }

    public function render(): View
    {
        $ticket = $this->findTicket()->load(['office', 'serviceType', 'history.actor', 'rating']);
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

        $canRate = in_array($ticket->status, [TicketStatus::Resolved, TicketStatus::Closed])
            && ! $this->ratingSubmitted;

        return view('livewire.portal.ticket-detail', compact('ticket', 'messages', 'recentTickets', 'canRate'));
    }

    private function findTicket(): Ticket
    {
        return Ticket::where('ulid', $this->ulid)
            ->where('requester_id', auth()->id())
            ->firstOrFail();
    }
}
