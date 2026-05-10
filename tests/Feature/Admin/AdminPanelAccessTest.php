<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

test('super_admin can access admin panel', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});

test('staff can access admin panel', function () {
    $user = User::factory()->create();
    $user->assignRole('staff');

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});

test('student cannot access admin panel', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

test('office_admin can access admin panel', function () {
    $user = User::factory()->create();
    $user->assignRole('office_admin');

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});

test('unauthenticated user is redirected from admin panel', function () {
    $this->get('/admin')->assertRedirect();
});
