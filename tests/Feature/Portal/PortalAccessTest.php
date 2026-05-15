<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('unauthenticated user is redirected from portal', function () {
    $this->get('/portal/tickets')->assertRedirect();
});

test('staff role cannot access portal', function () {
    $user = User::factory()->create();
    $user->assignRole('staff');

    $this->actingAs($user)
        ->get('/portal/tickets')
        ->assertForbidden();
});

test('student can access portal ticket list', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    $this->actingAs($user)
        ->get('/portal/tickets')
        ->assertOk();
});

test('portal named routes exist', function () {
    expect(route('portal.tickets.index'))->toContain('/portal/tickets');
    expect(route('portal.tickets.create'))->toContain('/portal/tickets/create');
    expect(route('portal.tickets.show', 'fake-ulid'))->toContain('/portal/tickets/fake-ulid');
});

test('unauthenticated user is redirected from ticket show route', function () {
    $this->get('/portal/tickets/some-ulid')->assertRedirect();
});
