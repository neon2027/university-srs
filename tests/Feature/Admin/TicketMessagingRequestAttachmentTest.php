<?php

use App\Livewire\Admin\TicketMessaging;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

function makeAdminTicket(): array
{
    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $student = User::factory()->create();
    $office = Office::factory()->create();
    $staff->offices()->attach($office, ['is_primary' => true]);

    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()
        ->for($student, 'requester')
        ->for($office)
        ->for($serviceType)
        ->create(['assigned_to' => $staff->id]);

    return compact('staff', 'ticket');
}

test('admin can send a reply with requests_attachment flag', function () {
    ['staff' => $staff, 'ticket' => $ticket] = makeAdminTicket();

    Livewire::actingAs($staff)
        ->test(TicketMessaging::class, ['ticket' => $ticket])
        ->set('body', 'Please upload your documents.')
        ->set('requestsAttachment', true)
        ->call('send')
        ->assertHasNoErrors();

    expect(
        TicketMessage::where('ticket_id', $ticket->id)
            ->where('requests_attachment', true)
            ->where('body', 'Please upload your documents.')
            ->exists()
    )->toBeTrue();
});

test('requests_attachment resets to false after send', function () {
    ['staff' => $staff, 'ticket' => $ticket] = makeAdminTicket();

    Livewire::actingAs($staff)
        ->test(TicketMessaging::class, ['ticket' => $ticket])
        ->set('body', 'Upload needed.')
        ->set('requestsAttachment', true)
        ->call('send')
        ->assertSet('requestsAttachment', false);
});

test('requests_attachment is forced false when sending an internal note', function () {
    ['staff' => $staff, 'ticket' => $ticket] = makeAdminTicket();

    Livewire::actingAs($staff)
        ->test(TicketMessaging::class, ['ticket' => $ticket])
        ->set('body', 'Staff-only note.')
        ->set('isInternalNote', true)
        ->set('requestsAttachment', true)
        ->call('send')
        ->assertHasNoErrors();

    expect(
        TicketMessage::where('ticket_id', $ticket->id)
            ->where('is_internal_note', true)
            ->where('requests_attachment', false)
            ->where('body', 'Staff-only note.')
            ->exists()
    )->toBeTrue();
});

test('guest messages are displayed with guest_name and via-public-tracker badge', function () {
    ['staff' => $staff, 'ticket' => $ticket] = makeAdminTicket();

    TicketMessage::factory()->guestReply('Ana Reyes')->create([
        'ticket_id' => $ticket->id,
        'body' => 'I have uploaded the form.',
        'is_internal_note' => false,
    ]);

    Livewire::actingAs($staff)
        ->test(TicketMessaging::class, ['ticket' => $ticket])
        ->assertSee('Ana Reyes')
        ->assertSee('Via public tracker')
        ->assertSee('I have uploaded the form.');
});
