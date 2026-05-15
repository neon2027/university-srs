<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Office;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\User;

test('ticket number is generated from office acronym year and sequence', function () {
    $office = Office::factory()->create(['name' => 'Information Communication Technology Office']);

    $ticket = Ticket::factory()->for($office)->create();

    expect($ticket->ulid)->toMatch('/^ICTO-T-\d{2}-0001$/');
});

test('ticket number sequence increments per office and year', function () {
    $office = Office::factory()->create(['name' => 'Information Communication Technology Office']);

    $first = Ticket::factory()->for($office)->create();
    $second = Ticket::factory()->for($office)->create();

    expect($first->ulid)->toEndWith('-0001')
        ->and($second->ulid)->toEndWith('-0002');
});

test('ticket belongs to requester, office, and service type', function () {
    $user = User::factory()->create();
    $office = Office::factory()->create();
    $type = ServiceType::factory()->create();

    $ticket = Ticket::factory()->create([
        'requester_id' => $user->id,
        'office_id' => $office->id,
        'service_type_id' => $type->id,
    ]);

    expect($ticket->requester->id)->toBe($user->id);
    expect($ticket->office->id)->toBe($office->id);
    expect($ticket->serviceType->id)->toBe($type->id);
});

test('ticket status defaults to pending', function () {
    $ticket = Ticket::factory()->create();

    expect($ticket->status)->toBe(TicketStatus::Pending);
});

test('ticket priority defaults to normal', function () {
    $ticket = Ticket::factory()->create();

    expect($ticket->priority)->toBe(TicketPriority::Normal);
});

test('ticket stores custom fields as array', function () {
    $ticket = Ticket::factory()->create([
        'custom_fields' => ['1' => 'BU-2024-001', '2' => 'Second semester'],
    ]);

    expect($ticket->custom_fields['1'])->toBe('BU-2024-001');
});

test('assigned factory state sets status and assignee', function () {
    $assignee = User::factory()->create();
    $ticket = Ticket::factory()->assigned($assignee)->create();

    expect($ticket->status)->toBe(TicketStatus::Assigned);
    expect($ticket->assignee->id)->toBe($assignee->id);
});

test('resolved factory state sets status and resolved_at', function () {
    $ticket = Ticket::factory()->resolved()->create();

    expect($ticket->status)->toBe(TicketStatus::Resolved);
    expect($ticket->resolved_at)->not->toBeNull();
});
