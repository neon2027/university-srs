<?php

use App\Filament\Resources\OfficeResource\Pages\EditOffice;
use App\Models\Office;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('super_admin can see citizen_charter upload field on office edit', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $office = Office::factory()->create();

    $this->actingAs($admin);

    Livewire::test(EditOffice::class, ['record' => $office->id])
        ->assertFormFieldExists('citizen_charter');
});
