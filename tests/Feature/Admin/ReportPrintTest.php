<?php

use App\Enums\TicketStatus;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketRating;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('super admin can access report print page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $office = Office::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.reports.print', [
            'office' => $office->id,
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]))
        ->assertOk()
        ->assertSee($office->name);
});

test('office admin can only access their own office report', function () {
    $admin = User::factory()->create();
    $admin->assignRole('office_admin');
    $office = Office::factory()->create();
    $admin->offices()->attach($office->id);

    $this->actingAs($admin)
        ->get(route('admin.reports.print', [
            'office' => $office->id,
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]))
        ->assertOk();
});

test('office admin cannot access another offices report', function () {
    $admin = User::factory()->create();
    $admin->assignRole('office_admin');
    $myOffice = Office::factory()->create();
    $otherOffice = Office::factory()->create();
    $admin->offices()->attach($myOffice->id);

    $this->actingAs($admin)
        ->get(route('admin.reports.print', [
            'office' => $otherOffice->id,
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]))
        ->assertForbidden();
});

test('students cannot access the report print page', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $office = Office::factory()->create();

    $this->actingAs($student)
        ->get(route('admin.reports.print', [
            'office' => $office->id,
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]))
        ->assertForbidden();
});

test('report shows ticket statistics and ratings', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $office = Office::factory()->create(['name' => 'Registrar Office']);
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $student = User::factory()->create();
    $student->assignRole('student');

    $ticket = Ticket::factory()
        ->for($student, 'requester')
        ->for($office)
        ->for($serviceType)
        ->create(['status' => TicketStatus::Resolved, 'resolved_at' => now()]);

    TicketRating::create([
        'ticket_id' => $ticket->id,
        'rater_id' => $student->id,
        'overall_rating' => 5,
        'service_rating' => 4,
        'comment' => 'Excellent service!',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.reports.print', [
            'office' => $office->id,
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]))
        ->assertOk()
        ->assertSee('Registrar Office')
        ->assertSee('Client Satisfaction Ratings')
        ->assertSee('Performance by Service Type');
});

test('report validates required parameters', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get(route('admin.reports.print'))
        ->assertSessionHasErrors(['office', 'from', 'to']);
});
