<?php

use App\Models\Office;
use App\Models\User;

test('office has many staff users', function () {
    $office = Office::factory()->create();
    $staff = User::factory()->count(3)->create();

    $office->staff()->attach($staff->pluck('id'), ['is_primary' => false]);

    expect($office->staff)->toHaveCount(3);
});

test('user can belong to multiple offices', function () {
    $user = User::factory()->create();
    $offices = Office::factory()->count(2)->create();

    $offices->each(fn ($o) => $o->staff()->attach($user->id, ['is_primary' => false]));

    expect($user->offices)->toHaveCount(2);
});

test('user has a primary office', function () {
    $user = User::factory()->create();
    $primary = Office::factory()->create();
    $secondary = Office::factory()->create();

    $primary->staff()->attach($user->id, ['is_primary' => true]);
    $secondary->staff()->attach($user->id, ['is_primary' => false]);

    $primaryOffice = $user->primaryOffice();
    expect($primaryOffice)->not->toBeNull();
    expect($primaryOffice->id)->toBe($primary->id);
});

test('office active scope filters inactive offices', function () {
    Office::factory()->count(2)->create(['is_active' => true]);
    Office::factory()->create(['is_active' => false]);

    expect(Office::active()->count())->toBe(2);
});

test('office slug is auto-generated from name', function () {
    $office = Office::factory()->create(['name' => 'Information Technology Office', 'slug' => null]);

    expect($office->slug)->toBe('information-technology-office');
});
