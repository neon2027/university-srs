<?php

use App\Livewire\Portal\CreateTicket;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('mount pre-fills office, category, and service when serviceTypeId is provided', function () {
    $user = User::factory()->create(['onboarding_completed_at' => now()]);
    $user->assignRole('student');

    $office = Office::factory()->create(['is_active' => true]);
    $category = ServiceCategory::factory()->create(['office_id' => $office->id, 'is_active' => true]);
    $service = ServiceType::factory()->create(['service_category_id' => $category->id, 'is_active' => true]);

    $this->actingAs($user);

    Livewire::test(CreateTicket::class, ['prefillServiceTypeId' => $service->id])
        ->assertSet('officeId', $office->id)
        ->assertSet('serviceCategoryId', $category->id)
        ->assertSet('serviceTypeId', $service->id)
        ->assertSet('step', 4);
});

test('mount ignores unknown or inactive serviceTypeId', function () {
    $user = User::factory()->create(['onboarding_completed_at' => now()]);
    $user->assignRole('student');

    $this->actingAs($user);

    Livewire::test(CreateTicket::class, ['prefillServiceTypeId' => 999999])
        ->assertSet('officeId', null)
        ->assertSet('serviceTypeId', null)
        ->assertSet('step', 1);
});

test('mount starts at step 1 when no serviceTypeId is given', function () {
    $user = User::factory()->create(['onboarding_completed_at' => now()]);
    $user->assignRole('student');

    $this->actingAs($user);

    Livewire::test(CreateTicket::class)
        ->assertSet('step', 1)
        ->assertSet('officeId', null);
});
