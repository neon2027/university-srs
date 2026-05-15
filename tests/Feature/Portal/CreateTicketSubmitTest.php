<?php

use App\Enums\EventType;
use App\Enums\FieldType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Livewire\Portal\CreateTicket;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\ServiceTypeField;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('submit creates ticket with correct data', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create(['name' => 'Grade Report']);

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 5)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->call('submit');

    $ticket = Ticket::first();
    expect($ticket)->not->toBeNull()
        ->and($ticket->requester_id)->toBe($student->id)
        ->and($ticket->office_id)->toBe($office->id)
        ->and($ticket->service_type_id)->toBe($serviceType->id)
        ->and($ticket->status)->toBe(TicketStatus::Pending)
        ->and($ticket->priority)->toBe(TicketPriority::Normal)
        ->and($ticket->subject)->toBe('Grade Report')
        ->and($ticket->description)->not->toBeEmpty()
        ->and($ticket->ulid)->not->toBeEmpty();
});

test('submit creates initial ticket history row', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create(['name' => 'Grade Report']);

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 5)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->call('submit');

    $ticket = Ticket::first();
    $history = TicketHistory::where('ticket_id', $ticket->id)->first();

    expect($history)->not->toBeNull()
        ->and($history->event_type)->toBe(EventType::Created)
        ->and($history->actor_id)->toBe($student->id);
});

test('submit stores custom field answers in ticket', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create(['name' => 'Grade Report']);
    $field = ServiceTypeField::factory()->for($serviceType)->create([
        'field_type' => FieldType::Text,
        'is_required' => false,
    ]);

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 5)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->set("customFields.{$field->id}", 'For scholarship application')
        ->call('submit');

    $ticket = Ticket::first();
    expect($ticket->custom_fields[(string) $field->id])->toBe('For scholarship application')
        ->and($ticket->description)->toContain('For scholarship application');
});

test('submit redirects to ticket detail page', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create(['name' => 'Grade Report']);

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 5)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->call('submit')
        ->assertRedirect();
});

test('submit fails when office not selected', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 5)
        ->call('submit')
        ->assertHasErrors(['officeId']);
});

test('submit fails when required custom field is empty', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create(['name' => 'Grade Report']);
    $field = ServiceTypeField::factory()->for($serviceType)->create([
        'field_type' => FieldType::Text,
        'is_required' => true,
    ]);

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 5)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->call('submit')
        ->assertHasErrors(["customFields.{$field->id}"]);
});
