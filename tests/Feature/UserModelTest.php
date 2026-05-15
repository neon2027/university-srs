<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role;

test('four roles exist after seeding', function () {
    $this->seed(RoleSeeder::class);

    expect(Role::pluck('name')->sort()->values()->toArray())
        ->toBe(['office_admin', 'staff', 'student', 'super_admin']);
});

test('user can be assigned a role', function () {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('student');

    expect($user->fresh()->hasRole('student'))->toBeTrue();
});

test('user has google_id and avatar columns', function () {
    $user = User::factory()->create([
        'google_id' => 'google-123',
        'avatar' => 'https://example.com/avatar.jpg',
    ]);

    expect($user->google_id)->toBe('google-123');
    expect($user->avatar)->toBe('https://example.com/avatar.jpg');
});

test('user factory produces a user without password', function () {
    $user = User::factory()->create();

    expect(array_key_exists('password', $user->getAttributes()))->toBeFalse();
});

test('user has initials helper', function () {
    $user = User::factory()->create(['name' => 'Juan Dela Cruz']);

    expect($user->initials())->toBe('JD');
});
