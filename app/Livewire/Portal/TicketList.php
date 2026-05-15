<?php

namespace App\Livewire\Portal;

use App\Models\Ticket;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class TicketList extends Component
{
    public function render(): View
    {
        $tickets = Ticket::with(['office', 'serviceType'])
            ->where('requester_id', auth()->id())
            ->latest()
            ->limit(50)
            ->get();

        return view('livewire.portal.ticket-list', compact('tickets'));
    }
}
