<?php

use App\Livewire\Public\TicketTracker;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function makePublicTicket(string $requesterName = 'Juan Santos'): Ticket
{
    $user = User::factory()->create(['name' => $requesterName]);
    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();

    return Ticket::factory()
        ->for($user, 'requester')
        ->for($office)
        ->for($serviceType)
        ->create();
}

test('lookup form is shown by default', function () {
    Livewire::test(TicketTracker::class)
        ->assertSee('Track your Ticket')
        ->assertDontSee('Search another ticket');
});

test('wrong ticket number returns generic error', function () {
    Livewire::test(TicketTracker::class)
        ->set('ticketNumber', 'WRONG-T-99-9999')
        ->set('lastName', 'Santos')
        ->call('lookup')
        ->assertSet('lookupError', 'Ticket not found or details do not match.');
});

test('wrong last name returns generic error', function () {
    $ticket = makePublicTicket('Juan Santos');

    Livewire::test(TicketTracker::class)
        ->set('ticketNumber', $ticket->ulid)
        ->set('lastName', 'WrongName')
        ->call('lookup')
        ->assertSet('lookupError', 'Ticket not found or details do not match.');
});

test('correct credentials store ticket id in session', function () {
    $ticket = makePublicTicket('Juan Santos');

    Livewire::test(TicketTracker::class)
        ->set('ticketNumber', $ticket->ulid)
        ->set('lastName', 'Santos')
        ->call('lookup')
        ->assertSet('lookupError', '');

    expect(session('tracker.ticket_id'))->toBe($ticket->id);
});

test('last name match is case-insensitive', function () {
    $ticket = makePublicTicket('Maria Dela Cruz');

    Livewire::test(TicketTracker::class)
        ->set('ticketNumber', $ticket->ulid)
        ->set('lastName', 'dela cruz')
        ->call('lookup')
        ->assertSet('lookupError', '');
});

test('empty fields are rejected', function () {
    Livewire::test(TicketTracker::class)
        ->set('ticketNumber', '')
        ->set('lastName', '')
        ->call('lookup')
        ->assertHasErrors(['ticketNumber', 'lastName']);
});

test('detail view is shown when session is already set', function () {
    $ticket = makePublicTicket('Ana Reyes');
    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->assertSee($ticket->subject)
        ->assertSee('Search another ticket');
});

test('clear session returns to lookup form', function () {
    $ticket = makePublicTicket('Ana Reyes');
    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->call('clearSession')
        ->assertDontSee('Search another ticket')
        ->assertSee('Track your Ticket');
});

test('internal notes are not shown in detail state', function () {
    $ticket = makePublicTicket('Ana Reyes');
    $staff = User::factory()->create();

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Secret internal note',
        'is_internal_note' => true,
    ]);

    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->assertDontSee('Secret internal note');
});

test('public messages are shown in detail state', function () {
    $ticket = makePublicTicket('Ana Reyes');
    $staff = User::factory()->create();

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Your request is being processed.',
        'is_internal_note' => false,
    ]);

    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->assertSee('Your request is being processed.');
});

test('guest can send a text reply', function () {
    $ticket = makePublicTicket('Ana Reyes');
    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->set('replyBody', 'I have submitted all required documents.')
        ->call('sendReply')
        ->assertHasNoErrors()
        ->assertSet('replyBody', '');

    expect(
        TicketMessage::query()
            ->where('ticket_id', $ticket->id)
            ->whereNull('sender_id')
            ->where('guest_name', 'Ana Reyes')
            ->where('body', 'I have submitted all required documents.')
            ->exists()
    )->toBeTrue();
});

test('empty reply is rejected', function () {
    $ticket = makePublicTicket('Ana Reyes');
    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->set('replyBody', '')
        ->call('sendReply')
        ->assertHasErrors(['replyBody']);
});

test('upload button is not shown for messages without requests_attachment', function () {
    $ticket = makePublicTicket('Ana Reyes');
    $staff = User::factory()->create();

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Regular reply, no attachment needed.',
        'is_internal_note' => false,
        'requests_attachment' => false,
    ]);

    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->assertDontSee('Attachment requested');
});

test('upload button is shown for messages with requests_attachment', function () {
    $ticket = makePublicTicket('Ana Reyes');
    $staff = User::factory()->create();

    TicketMessage::factory()->requestingAttachment()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Please upload your clearance.',
        'is_internal_note' => false,
    ]);

    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->assertSee('Attachment requested');
});

test('guest can upload an attachment for a requesting message', function () {
    Storage::fake('local');

    $ticket = makePublicTicket('Ana Reyes');
    $staff = User::factory()->create();

    $requestingMessage = TicketMessage::factory()->requestingAttachment()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Please provide your certificate.',
        'is_internal_note' => false,
    ]);

    session(['tracker.ticket_id' => $ticket->id]);

    $file = UploadedFile::fake()->create('certificate.pdf', 200, 'application/pdf');

    Livewire::test(TicketTracker::class)
        ->set("attachmentFiles.{$requestingMessage->id}", $file)
        ->call('uploadAttachment', $requestingMessage->id)
        ->assertHasNoErrors();

    expect(
        TicketAttachment::query()
            ->where('ticket_id', $ticket->id)
            ->where('ticket_message_id', $requestingMessage->id)
            ->whereNull('uploader_id')
            ->where('original_filename', 'certificate.pdf')
            ->exists()
    )->toBeTrue();
});

test('invalid file type is rejected on upload', function () {
    Storage::fake('local');

    $ticket = makePublicTicket('Ana Reyes');
    $staff = User::factory()->create();

    $requestingMessage = TicketMessage::factory()->requestingAttachment()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Upload needed.',
        'is_internal_note' => false,
    ]);

    session(['tracker.ticket_id' => $ticket->id]);

    $file = UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');

    Livewire::test(TicketTracker::class)
        ->set("attachmentFiles.{$requestingMessage->id}", $file)
        ->call('uploadAttachment', $requestingMessage->id)
        ->assertHasErrors(["attachmentFiles.{$requestingMessage->id}"]);
});
