<?php

use App\Livewire\Admin\TicketMessaging;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('admin can send a message to student', function () {
    $admin = User::factory()->create();
    $admin->assignRole('staff');
    $ticket = Ticket::factory()->create();
    $admin->offices()->attach($ticket->office_id);

    $this->actingAs($admin);

    Livewire::test(TicketMessaging::class, ['ticket' => $ticket])
        ->set('body', 'Hello from staff')
        ->set('isInternalNote', false)
        ->call('send')
        ->assertSet('body', '');

    expect(TicketMessage::where('ticket_id', $ticket->id)
        ->where('body', 'Hello from staff')
        ->where('is_internal_note', false)
        ->exists())->toBeTrue();
});

test('assigned staff can send a message even when outside ticket office', function () {
    $admin = User::factory()->create();
    $admin->assignRole('staff');
    $ticket = Ticket::factory()->create(['assigned_to' => $admin->id]);

    $this->actingAs($admin);

    Livewire::test(TicketMessaging::class, ['ticket' => $ticket])
        ->set('body', 'I am handling this request')
        ->call('send')
        ->assertSet('errorMessage', null)
        ->assertSet('body', '');

    expect(TicketMessage::where('ticket_id', $ticket->id)
        ->where('sender_id', $admin->id)
        ->where('body', 'I am handling this request')
        ->exists())->toBeTrue();
});

test('unauthorized staff sees an error when sending outside assigned or office tickets', function () {
    $admin = User::factory()->create();
    $admin->assignRole('staff');
    $ticket = Ticket::factory()->create();

    $this->actingAs($admin);

    Livewire::test(TicketMessaging::class, ['ticket' => $ticket])
        ->set('body', 'This should not send')
        ->call('send')
        ->assertSet('errorMessage', 'You can only reply to tickets assigned to you or tickets from your office.');

    expect(TicketMessage::where('ticket_id', $ticket->id)
        ->where('body', 'This should not send')
        ->exists())->toBeFalse();
});

test('admin can send an internal note invisible to student', function () {
    $admin = User::factory()->create();
    $admin->assignRole('staff');
    $ticket = Ticket::factory()->create();
    $admin->offices()->attach($ticket->office_id);

    $this->actingAs($admin);

    Livewire::test(TicketMessaging::class, ['ticket' => $ticket])
        ->set('body', 'Internal staff note')
        ->set('isInternalNote', true)
        ->call('send')
        ->assertHasNoErrors();

    expect(TicketMessage::where('ticket_id', $ticket->id)
        ->where('is_internal_note', true)
        ->exists())->toBeTrue();
});

test('admin can use canned response to prefill message', function () {
    $admin = User::factory()->create();
    $admin->assignRole('staff');
    $ticket = Ticket::factory()->create();

    $this->actingAs($admin);

    Livewire::test(TicketMessaging::class, ['ticket' => $ticket])
        ->call('applyCannedResponse', 'Thank you for contacting us.')
        ->assertSet('body', 'Thank you for contacting us.');
});
