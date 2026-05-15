<?php

use App\Enums\OnboardingStatus;
use App\Models\Office;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('verify employee command approves pending office request', function () {
    $office = Office::factory()->create();
    $user = User::factory()->create([
        'onboarding_status' => OnboardingStatus::PendingEmployee,
        'pending_office_id' => $office->id,
        'onboarding_completed_at' => now(),
    ]);
    $user->assignRole('student');

    $this->artisan('users:verify-employee', ['email' => $user->email])
        ->expectsOutput("Verified {$user->email} as staff for {$office->name}.")
        ->assertExitCode(0);

    $user->refresh();

    expect($user->hasRole('staff'))->toBeTrue()
        ->and($user->offices()->whereKey($office->id)->exists())->toBeTrue()
        ->and($user->offices()->whereKey($office->id)->first()->pivot->is_primary)->toBe(1)
        ->and($user->onboarding_status)->toBeNull()
        ->and($user->pending_office_id)->toBeNull()
        ->and($user->onboarding_completed_at)->not->toBeNull();
});

test('verify employee command can use an office override', function () {
    $pendingOffice = Office::factory()->create();
    $targetOffice = Office::factory()->create();
    $user = User::factory()->create([
        'onboarding_status' => OnboardingStatus::PendingEmployee,
        'pending_office_id' => $pendingOffice->id,
        'onboarding_completed_at' => now(),
    ]);

    $this->artisan('users:verify-employee', [
        'email' => $user->email,
        '--office' => $targetOffice->slug,
        '--role' => 'office_admin',
    ])->assertExitCode(0);

    $user->refresh();

    expect($user->hasRole('office_admin'))->toBeTrue()
        ->and($user->offices()->whereKey($targetOffice->id)->exists())->toBeTrue()
        ->and($user->pending_office_id)->toBeNull();
});
