<?php

use App\Enums\EventType;
use App\Enums\TicketStatus;
use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('admin can change ticket status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Pending]);

    $this->actingAs($admin);

    Livewire::test(ViewTicket::class, ['record' => $ticket->ulid])
        ->callAction('change_status', data: ['status' => TicketStatus::InProgress->value])
        ->assertHasNoActionErrors();

    expect($ticket->fresh()->status)->toBe(TicketStatus::InProgress);
});

test('changing status creates a history entry', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Pending]);

    $this->actingAs($admin);

    Livewire::test(ViewTicket::class, ['record' => $ticket->ulid])
        ->callAction('change_status', data: ['status' => TicketStatus::InProgress->value])
        ->assertHasNoActionErrors();

    expect(
        TicketHistory::where('ticket_id', $ticket->id)
            ->where('event_type', EventType::StatusChanged)
            ->where('from_status', TicketStatus::Pending)
            ->where('to_status', TicketStatus::InProgress)
            ->exists()
    )->toBeTrue();
});

test('resolving a ticket sets resolved_at timestamp', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $ticket = Ticket::factory()->create(['status' => TicketStatus::InProgress, 'resolved_at' => null]);

    $this->actingAs($admin);

    Livewire::test(ViewTicket::class, ['record' => $ticket->ulid])
        ->callAction('change_status', data: ['status' => TicketStatus::Resolved->value])
        ->assertHasNoActionErrors();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Resolved);
    expect($ticket->resolved_at)->not->toBeNull();
});

test('changing status to resolved creates resolved history event', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $ticket = Ticket::factory()->create(['status' => TicketStatus::InProgress]);

    $this->actingAs($admin);

    Livewire::test(ViewTicket::class, ['record' => $ticket->ulid])
        ->callAction('change_status', data: ['status' => TicketStatus::Resolved->value])
        ->assertHasNoActionErrors();

    expect(
        TicketHistory::where('ticket_id', $ticket->id)
            ->where('event_type', EventType::Resolved)
            ->exists()
    )->toBeTrue();
});

test('re-opening a ticket clears resolved_at', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $ticket = Ticket::factory()->create([
        'status' => TicketStatus::Resolved,
        'resolved_at' => now(),
    ]);

    $this->actingAs($admin);

    Livewire::test(ViewTicket::class, ['record' => $ticket->ulid])
        ->callAction('change_status', data: ['status' => TicketStatus::InProgress->value])
        ->assertHasNoActionErrors();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::InProgress);
    expect($ticket->resolved_at)->toBeNull();
});

test('same status change is a no-op', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Pending]);

    $this->actingAs($admin);

    Livewire::test(ViewTicket::class, ['record' => $ticket->ulid])
        ->callAction('change_status', data: ['status' => TicketStatus::Pending->value])
        ->assertHasNoActionErrors();

    expect(TicketHistory::where('ticket_id', $ticket->id)->count())->toBe(0);
});
