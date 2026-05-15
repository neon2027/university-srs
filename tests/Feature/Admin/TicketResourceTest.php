<?php

use App\Enums\TicketStatus;
use App\Filament\Resources\TicketResource\Pages\ListTickets;
use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('super_admin sees all tickets', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    Ticket::factory()->count(5)->create();

    $this->actingAs($admin);

    Livewire::test(ListTickets::class)
        ->assertCountTableRecords(5);
});

test('scoped roles only see their office tickets', function (string $role) {
    $office = Office::factory()->create();
    $otherOffice = Office::factory()->create();

    $user = User::factory()->create();
    $user->assignRole($role);
    $user->offices()->attach($office, ['is_primary' => true]);

    Ticket::factory()->count(3)->create(['office_id' => $office->id]);
    Ticket::factory()->count(2)->create(['office_id' => $otherOffice->id]);

    $this->actingAs($user);

    Livewire::test(ListTickets::class)
        ->assertCountTableRecords(3);
})->with(['staff', 'office_admin']);

test('ticket table shows status badge', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    Ticket::factory()->create(['status' => TicketStatus::Pending]);

    $this->actingAs($admin);

    Livewire::test(ListTickets::class)
        ->assertSee('Pending');
});

test('ticket view renders workspace layout', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $ticket = Ticket::factory()->create(['subject' => 'Payment Posting Concern']);

    $this->actingAs($admin);

    Livewire::test(ViewTicket::class, ['record' => $ticket->ulid])
        ->assertSee('All tickets')
        ->assertSee('Payment Posting Concern')
        ->assertSee('Contact Details')
        ->assertSee('Service Task');
});
