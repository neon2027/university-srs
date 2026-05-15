<?php

use App\Enums\TicketStatus;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('student sees only their own tickets', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $other = User::factory()->create();
    $other->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();

    $mine = Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create(['subject' => 'My Request']);
    Ticket::factory()->for($other, 'requester')->for($office)->for($serviceType)->create(['subject' => 'Their Request']);

    $this->actingAs($student)
        ->get(route('portal.tickets.index'))
        ->assertSee('My Request')
        ->assertDontSee('Their Request');
});

test('empty state shown when student has no tickets', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $this->actingAs($student)
        ->get(route('portal.tickets.index'))
        ->assertSee('No requests yet');
});

test('ticket status badge is shown', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create([
        'status' => TicketStatus::Pending,
    ]);

    $this->actingAs($student)
        ->get(route('portal.tickets.index'))
        ->assertSee('Pending');
});

test('ticket ulid is shown on list', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create();

    $this->actingAs($student)
        ->get(route('portal.tickets.index'))
        ->assertSee($ticket->ulid);
});
