<?php

use App\Livewire\Portal\TicketDetail;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('student sees non-internal messages', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $staff = User::factory()->create();

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create();

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Public reply',
        'is_internal_note' => false,
    ]);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->assertSee('Public reply');
});

test('student does not see internal notes', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $staff = User::factory()->create();

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create();

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Staff internal note',
        'is_internal_note' => true,
    ]);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->assertDontSee('Staff internal note');
});

test('student can send a message', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create();

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->set('messageBody', 'Any update on my request?')
        ->call('sendMessage')
        ->assertHasNoErrors()
        ->assertSet('messageBody', '');

    expect(
        TicketMessage::where('ticket_id', $ticket->id)
            ->where('sender_id', $student->id)
            ->where('body', 'Any update on my request?')
            ->where('is_internal_note', false)
            ->exists()
    )->toBeTrue();
});

test('empty message is rejected', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create();

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->set('messageBody', '')
        ->call('sendMessage')
        ->assertHasErrors(['messageBody']);
});

test('opening ticket detail marks staff messages as seen', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $staff = User::factory()->create();

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create();

    $message = TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Hello',
        'is_internal_note' => false,
        'seen_at' => null,
    ]);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid]);

    expect($message->fresh()->seen_at)->not->toBeNull();
});

test('opening ticket detail does not mark own messages as seen', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create();

    $ownMessage = TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $student->id,
        'body' => 'My own message',
        'is_internal_note' => false,
        'seen_at' => null,
    ]);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid]);

    expect($ownMessage->fresh()->seen_at)->toBeNull();
});

test('opening ticket detail does not mark internal notes as seen', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $staff = User::factory()->create();

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create();

    $internalNote = TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Internal note',
        'is_internal_note' => true,
        'seen_at' => null,
    ]);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid]);

    expect($internalNote->fresh()->seen_at)->toBeNull();
});
