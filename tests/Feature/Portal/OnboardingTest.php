<?php

use App\Enums\OnboardingStatus;
use App\Livewire\Portal\Onboarding;
use App\Models\Office;
use App\Models\User;
use App\Notifications\EmployeeVerificationRequestedNotification;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('employee onboarding request notifies office admins', function () {
    Notification::fake();

    $office = Office::factory()->create();
    $admin = User::factory()->create();
    $admin->assignRole('office_admin');
    $office->staff()->attach($admin->id);

    $employee = User::factory()->needsOnboarding()->create();

    Livewire::actingAs($employee)
        ->test(Onboarding::class)
        ->set('selectedOfficeId', $office->id)
        ->call('submitEmployeeRequest')
        ->assertRedirect(route('portal.tickets.index'));

    $employee->refresh();

    expect($employee->onboarding_status)->toBe(OnboardingStatus::PendingEmployee)
        ->and($employee->pending_office_id)->toBe($office->id)
        ->and($employee->onboarding_completed_at)->not->toBeNull();

    Notification::assertSentTo($admin, EmployeeVerificationRequestedNotification::class);
});
