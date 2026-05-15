<?php

use App\Enums\FieldType;
use App\Livewire\Portal\CreateTicket;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\ServiceTypeField;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('required text field blocks advance when empty', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $field = ServiceTypeField::factory()->for($serviceType)->create([
        'field_type' => FieldType::Text,
        'is_required' => true,
    ]);

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 4)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->call('nextStep')
        ->assertSet('step', 4)
        ->assertHasErrors(["customFields.{$field->id}"]);
});

test('optional field allows advance when empty', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    ServiceTypeField::factory()->for($serviceType)->create([
        'field_type' => FieldType::Text,
        'is_required' => false,
    ]);

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 4)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->call('nextStep')
        ->assertSet('step', 5)
        ->assertHasNoErrors();
});

test('filled required field allows advance', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $field = ServiceTypeField::factory()->for($serviceType)->create([
        'field_type' => FieldType::Text,
        'is_required' => true,
    ]);

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 4)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->set("customFields.{$field->id}", 'BU-2024-001')
        ->call('nextStep')
        ->assertSet('step', 5)
        ->assertHasNoErrors();
});

test('service type fields are shown in step 4', function () {
    $student = User::factory()->create();
    $student->assignRole('student');
    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    ServiceTypeField::factory()->for($serviceType)->create([
        'label' => 'Student ID Number',
        'field_type' => FieldType::Text,
        'is_required' => true,
    ]);

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 4)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->assertSee('Student ID Number');
});
