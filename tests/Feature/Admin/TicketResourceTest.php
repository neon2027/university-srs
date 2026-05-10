<?php

use App\Enums\TicketStatus;
use App\Filament\Resources\TicketResource\Pages\ListTickets;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

function makeStaff(Office $office): User
{
    $user = User::factory()->create();
    $user->assignRole('staff');
    $user->offices()->attach($office, ['is_primary' => true]);

    return $user;
}

test('super_admin sees all tickets', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    Ticket::factory()->count(5)->create();

    $this->actingAs($admin);

    Livewire::test(ListTickets::class)
        ->assertCountTableRecords(5);
});

test('staff only sees their office tickets', function () {
    $office = Office::factory()->create();
    $otherOffice = Office::factory()->create();
    $staff = makeStaff($office);

    Ticket::factory()->count(3)->create(['office_id' => $office->id]);
    Ticket::factory()->count(2)->create(['office_id' => $otherOffice->id]);

    $this->actingAs($staff);

    Livewire::test(ListTickets::class)
        ->assertCountTableRecords(3);
});

test('ticket table shows status badge', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    Ticket::factory()->create(['status' => TicketStatus::Pending]);

    $this->actingAs($admin);

    Livewire::test(ListTickets::class)
        ->assertSee('Pending');
});
