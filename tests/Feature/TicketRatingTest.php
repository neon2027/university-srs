<?php

use App\Enums\TicketStatus;
use App\Livewire\Portal\TicketDetail;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

function makeResolvedTicket(User $student): Ticket
{
    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();

    return Ticket::factory()
        ->for($student, 'requester')
        ->for($office)
        ->for($serviceType)
        ->create(['status' => TicketStatus::Resolved, 'resolved_at' => now()]);
}

test('rating form is shown for resolved tickets', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $ticket = makeResolvedTicket($student);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->assertSee('Rate this service');
});

test('rating form is not shown for pending tickets', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()
        ->for($student, 'requester')
        ->for($office)
        ->for($serviceType)
        ->create(['status' => TicketStatus::Pending]);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->assertDontSee('Rate this service');
});

test('student can submit a rating for a resolved ticket', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $ticket = makeResolvedTicket($student);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->set('overallRating', 5)
        ->set('serviceRating', 4)
        ->set('ratingComment', 'Very helpful service.')
        ->call('submitRating')
        ->assertSet('ratingSubmitted', true);

    $rating = TicketRating::where('ticket_id', $ticket->id)->first();
    expect($rating)->not->toBeNull();
    expect($rating->overall_rating)->toBe(5);
    expect($rating->service_rating)->toBe(4);
    expect($rating->comment)->toBe('Very helpful service.');
    expect($rating->rater_id)->toBe($student->id);
});

test('rating requires overall and service scores', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $ticket = makeResolvedTicket($student);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->set('overallRating', 0)
        ->set('serviceRating', 0)
        ->call('submitRating')
        ->assertHasErrors(['overallRating', 'serviceRating']);

    expect(TicketRating::where('ticket_id', $ticket->id)->count())->toBe(0);
});

test('student cannot rate the same ticket twice', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $ticket = makeResolvedTicket($student);

    TicketRating::create([
        'ticket_id' => $ticket->id,
        'rater_id' => $student->id,
        'overall_rating' => 4,
        'service_rating' => 4,
    ]);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->assertSet('ratingSubmitted', true)
        ->assertDontSee('Rate this service');
});

test('rating is not saved for non-resolved tickets', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()
        ->for($student, 'requester')
        ->for($office)
        ->for($serviceType)
        ->create(['status' => TicketStatus::InProgress]);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->set('overallRating', 5)
        ->set('serviceRating', 5)
        ->call('submitRating');

    expect(TicketRating::where('ticket_id', $ticket->id)->count())->toBe(0);
});
