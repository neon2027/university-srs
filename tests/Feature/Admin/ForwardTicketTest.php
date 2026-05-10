<?php

use App\Enums\CreditType;
use App\Enums\EventType;
use App\Enums\TicketStatus;
use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Models\ForwardingLog;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('forward action creates forwarding log and ticket history', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $fromOffice = Office::factory()->create();
    $toOffice = Office::factory()->create();
    $ticket = Ticket::factory()->create([
        'office_id' => $fromOffice->id,
        'status' => TicketStatus::Pending,
    ]);

    $this->actingAs($admin);

    Livewire::test(ViewTicket::class, ['record' => $ticket->ulid])
        ->callAction('forward_ticket', data: [
            'to_office_id' => $toOffice->id,
            'credit_type' => CreditType::AcceptCredit->value,
            'note' => 'Please handle this',
        ])
        ->assertHasNoActionErrors();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Forwarded)
        ->and($ticket->office_id)->toBe($toOffice->id);

    expect(ForwardingLog::where('ticket_id', $ticket->id)->exists())->toBeTrue();

    $log = ForwardingLog::where('ticket_id', $ticket->id)->first();
    expect($log->from_office_id)->toBe($fromOffice->id)
        ->and($log->to_office_id)->toBe($toOffice->id)
        ->and($log->credit_type->value)->toBe(CreditType::AcceptCredit->value);

    expect(TicketHistory::where('ticket_id', $ticket->id)
        ->where('event_type', EventType::Forwarded)
        ->exists())->toBeTrue();
});
