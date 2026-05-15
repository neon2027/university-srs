<?php

use App\Enums\EventType;
use App\Livewire\Portal\TicketDetail;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('student can view their ticket detail page', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create();

    $this->actingAs($student)
        ->get(route('portal.tickets.show', $ticket->ulid))
        ->assertOk()
        ->assertSee($ticket->subject);
});

test('student cannot view another students ticket', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $other = User::factory()->create();
    $other->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()->for($other, 'requester')->for($office)->for($serviceType)->create();

    $this->actingAs($student)
        ->get(route('portal.tickets.show', $ticket->ulid))
        ->assertNotFound();
});

test('timeline shows history events in chronological order', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create();

    TicketHistory::factory()->create([
        'ticket_id' => $ticket->id,
        'actor_id' => $student->id,
        'event_type' => EventType::Created,
        'created_at' => now()->subMinutes(10),
    ]);
    TicketHistory::factory()->create([
        'ticket_id' => $ticket->id,
        'actor_id' => $student->id,
        'event_type' => EventType::Assigned,
        'created_at' => now()->subMinutes(5),
    ]);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->assertSeeInOrder(['Created', 'Assigned']);
});

test('ticket office and service type name are shown', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create();
    $ticket->load(['office', 'serviceType']);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->assertSee($ticket->office->name)
        ->assertSee($ticket->serviceType->name);
});
