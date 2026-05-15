<?php

use App\Livewire\Portal\CreateTicket;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('form starts on step 1', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->assertSet('step', 1);
});

test('cannot advance from step 1 without an office', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->call('nextStep')
        ->assertSet('step', 1)
        ->assertHasErrors(['officeId']);
});

test('advances to step 2 after selecting office', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $office = Office::factory()->create();

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('officeId', $office->id)
        ->call('nextStep')
        ->assertSet('step', 2)
        ->assertHasNoErrors();
});

test('categories shown are filtered to selected office', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $office = Office::factory()->create();
    $other = Office::factory()->create();
    ServiceCategory::factory()->for($office)->create(['name' => 'My Category']);
    ServiceCategory::factory()->for($other)->create(['name' => 'Other Category']);

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('officeId', $office->id)
        ->set('step', 2)
        ->assertSee('My Category')
        ->assertDontSee('Other Category');
});

test('changing office resets category and service', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $office1 = Office::factory()->create();
    $office2 = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office1)->create();

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('officeId', $office1->id)
        ->set('serviceCategoryId', $category->id)
        ->set('officeId', $office2->id)
        ->assertSet('serviceCategoryId', null)
        ->assertSet('serviceTypeId', null);
});

test('advances to step 3 after selecting category', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('officeId', $office->id)
        ->set('step', 2)
        ->set('serviceCategoryId', $category->id)
        ->call('nextStep')
        ->assertSet('step', 3)
        ->assertHasNoErrors();
});

test('advances to step 4 after selecting service type', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('step', 3)
        ->set('serviceTypeId', $serviceType->id)
        ->call('nextStep')
        ->assertSet('step', 4)
        ->assertHasNoErrors();
});

test('can navigate back from step 2 to step 1', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 2)
        ->call('prevStep')
        ->assertSet('step', 1);
});

test('nextStep does not advance past step 5', function () {
    $student = User::factory()->create();
    $student->assignRole('student');

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 5)
        ->call('nextStep')
        ->assertSet('step', 5);
});
