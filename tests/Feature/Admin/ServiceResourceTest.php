<?php

use App\Filament\Resources\ServiceCategoryResource\Pages\CreateServiceCategory;
use App\Filament\Resources\ServiceCategoryResource\Pages\ListServiceCategories;
use App\Filament\Resources\ServiceTypeResource\Pages\CreateServiceType;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $this->actingAs($user);
});

test('service category resource lists categories', function () {
    ServiceCategory::factory()->count(3)->create();

    Livewire::test(ListServiceCategories::class)
        ->assertSuccessful()
        ->assertCountTableRecords(3);
});

test('can create a service category', function () {
    $office = Office::factory()->create();

    Livewire::test(CreateServiceCategory::class)
        ->fillForm([
            'office_id' => $office->id,
            'name' => 'Academic Records',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(ServiceCategory::where('name', 'Academic Records')->exists())->toBeTrue();
});

test('can create a service type with SLA', function () {
    $category = ServiceCategory::factory()->create();

    Livewire::test(CreateServiceType::class)
        ->fillForm([
            'service_category_id' => $category->id,
            'name' => 'Transcript Request',
            'sla_days' => 5,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});
