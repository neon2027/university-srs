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

test('assign action updates ticket status and creates history', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $staff = User::factory()->create();
    $staff->assignRole('staff');
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Pending]);

    $this->actingAs($admin);

    Livewire::test(ViewTicket::class, ['record' => $ticket->ulid])
        ->callAction('assign_ticket', data: ['assignee_id' => $staff->id])
        ->assertHasNoActionErrors();

    $ticket->refresh();
    expect($ticket->assigned_to)->toBe($staff->id)
        ->and($ticket->status)->toBe(TicketStatus::Assigned);

    expect(TicketHistory::where('ticket_id', $ticket->id)
        ->where('event_type', EventType::Assigned)
        ->exists())->toBeTrue();
});
