<?php

use App\Livewire\Public\OfficeDetail;
use App\Livewire\Public\OfficeList;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

test('office list shows active offices', function () {
    $active = Office::factory()->create(['name' => 'Finance Office', 'is_active' => true]);
    $inactive = Office::factory()->create(['name' => 'Hidden Office', 'is_active' => false]);

    Livewire::test(OfficeList::class)
        ->assertSee('Finance Office')
        ->assertDontSee('Hidden Office');
});

test('office list page is publicly accessible', function () {
    Office::factory()->create(['is_active' => true]);

    $this->get(route('offices.index'))
        ->assertOk();
});

test('office detail shows office name and services', function () {
    $office = Office::factory()->create(['is_active' => true]);
    $category = ServiceCategory::factory()->create(['office_id' => $office->id, 'is_active' => true]);
    $service = ServiceType::factory()->create(['service_category_id' => $category->id, 'is_active' => true]);

    Livewire::test(OfficeDetail::class, ['slug' => $office->slug])
        ->assertSee($office->name)
        ->assertSee($category->name)
        ->assertSee($service->name);
});

test('office detail shows citizen charter viewer when file is set', function () {
    $office = Office::factory()->create([
        'is_active' => true,
        'citizen_charter' => 'citizen-charters/charter.pdf',
    ]);

    Livewire::test(OfficeDetail::class, ['slug' => $office->slug])
        ->assertSee('View Citizen Charter');
});

test('office detail hides citizen charter viewer when no file', function () {
    $office = Office::factory()->create(['is_active' => true, 'citizen_charter' => null]);

    Livewire::test(OfficeDetail::class, ['slug' => $office->slug])
        ->assertDontSee('View Citizen Charter');
});

test('office detail shows work instruction viewer for service when file is set', function () {
    $office = Office::factory()->create(['is_active' => true]);
    $category = ServiceCategory::factory()->create(['office_id' => $office->id, 'is_active' => true]);
    ServiceType::factory()->create([
        'service_category_id' => $category->id,
        'is_active' => true,
        'work_instruction' => 'work-instructions/guide.pdf',
    ]);

    Livewire::test(OfficeDetail::class, ['slug' => $office->slug])
        ->assertSee('View Work Instruction');
});

test('office detail shows request button for authenticated onboarded student', function () {
    $this->seed(RoleSeeder::class);
    $user = User::factory()->create(['onboarding_completed_at' => now()]);
    $user->assignRole('student');

    $office = Office::factory()->create(['is_active' => true]);
    $category = ServiceCategory::factory()->create(['office_id' => $office->id, 'is_active' => true]);
    ServiceType::factory()->create(['service_category_id' => $category->id, 'is_active' => true]);

    $this->actingAs($user);

    Livewire::test(OfficeDetail::class, ['slug' => $office->slug])
        ->assertSee('Request this service');
});

test('office detail shows sign in link for guests', function () {
    $office = Office::factory()->create(['is_active' => true]);
    $category = ServiceCategory::factory()->create(['office_id' => $office->id, 'is_active' => true]);
    ServiceType::factory()->create(['service_category_id' => $category->id, 'is_active' => true]);

    Livewire::test(OfficeDetail::class, ['slug' => $office->slug])
        ->assertSee('Sign in to request');
});

test('office detail returns 404 for inactive office', function () {
    $office = Office::factory()->create(['is_active' => false]);

    $this->get(route('offices.show', $office->slug))
        ->assertNotFound();
});
