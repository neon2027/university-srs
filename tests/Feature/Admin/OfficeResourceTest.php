<?php

use App\Filament\Resources\OfficeResource\Pages\CreateOffice;
use App\Filament\Resources\OfficeResource\Pages\ListOffices;
use App\Models\Office;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $this->actingAs($user);
});

test('office resource lists offices', function () {
    Office::factory()->count(3)->create();

    Livewire::test(ListOffices::class)
        ->assertSuccessful()
        ->assertCountTableRecords(3);
});

test('super_admin can create an office', function () {
    Livewire::test(CreateOffice::class)
        ->fillForm([
            'name' => 'Health Services Office',
            'email' => 'health@bicol-u.edu.ph',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Office::where('name', 'Health Services Office')->exists())->toBeTrue();
});

test('office resource is restricted to super_admin only', function () {
    $staff = User::factory()->create();
    $staff->assignRole('staff');
    $this->actingAs($staff);

    $this->get('/admin/offices')->assertForbidden();
});
