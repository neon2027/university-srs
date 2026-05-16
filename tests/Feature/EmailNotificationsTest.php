<?php

use App\Enums\TicketStatus;
use App\Livewire\Admin\TicketMessaging;
use App\Livewire\Portal\CreateTicket;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketReplyNotification;
use App\Notifications\TicketResolvedNotification;
use App\Notifications\TicketSubmittedNotification;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('submission confirmation email is sent to requester after ticket creation', function () {
    Notification::fake();

    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create(['name' => 'Test Service']);

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->call('submit');

    Notification::assertSentTo($student, TicketSubmittedNotification::class);
});

test('reply notification is sent to requester on public admin reply', function () {
    Notification::fake();

    $student = User::factory()->create();
    $student->assignRole('student');

    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();

    $ticket = Ticket::factory()
        ->for($student, 'requester')
        ->for($office)
        ->for($serviceType)
        ->create(['assigned_to' => $staff->id]);

    $staff->offices()->attach($office->id);

    Livewire::actingAs($staff)
        ->test(TicketMessaging::class, ['ticket' => $ticket])
        ->set('body', 'We are looking into your request.')
        ->set('isInternalNote', false)
        ->call('send');

    Notification::assertSentTo($student, TicketReplyNotification::class);
});

test('reply notification is not sent for internal notes', function () {
    Notification::fake();

    $student = User::factory()->create();
    $student->assignRole('student');

    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();

    $ticket = Ticket::factory()
        ->for($student, 'requester')
        ->for($office)
        ->for($serviceType)
        ->create(['assigned_to' => $staff->id]);

    $staff->offices()->attach($office->id);

    Livewire::actingAs($staff)
        ->test(TicketMessaging::class, ['ticket' => $ticket])
        ->set('body', 'Internal note for the team.')
        ->set('isInternalNote', true)
        ->call('send');

    Notification::assertNotSentTo($student, TicketReplyNotification::class);
});

test('resolved notification is sent when ticket status changes to resolved', function () {
    Notification::fake();

    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();

    $ticket = Ticket::factory()
        ->for($student, 'requester')
        ->for($office)
        ->for($serviceType)
        ->create(['status' => TicketStatus::Resolved]);

    $ticket->requester->notify(new TicketResolvedNotification($ticket));

    Notification::assertSentTo($student, TicketResolvedNotification::class);
});

test('resolved notification mail contains rate experience link', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();

    $ticket = Ticket::factory()
        ->for($student, 'requester')
        ->for($office)
        ->for($serviceType)
        ->create(['status' => TicketStatus::Resolved]);

    $notification = new TicketResolvedNotification($ticket);
    $mail = $notification->toMail($student);

    expect($mail->subject)->toContain('Resolved');
    expect($mail->actionUrl)->toBe(route('portal.tickets.show', $ticket->ulid));
    expect($mail->actionText)->toBe('Rate Your Experience');
});
