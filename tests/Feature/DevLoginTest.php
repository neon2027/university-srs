<?php

use App\Livewire\Dev\LoginPanel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
});

// HTTP controller tests
it('logs in as a student and redirects to portal', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    $this->post(route('dev.login'), ['user_id' => $user->id])
        ->assertRedirect(route('portal.tickets.index'));

    $this->assertAuthenticatedAs($user);
});

it('logs in as an admin and redirects to admin panel', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $this->post(route('dev.login'), ['user_id' => $user->id])
        ->assertRedirect('/admin');

    $this->assertAuthenticatedAs($user);
});

it('redirects student with incomplete onboarding to onboarding page', function () {
    $user = User::factory()->needsOnboarding()->create();
    $user->assignRole('student');

    $this->post(route('dev.login'), ['user_id' => $user->id])
        ->assertRedirect(route('portal.onboarding'));
});

it('returns 404 for non-existent user', function () {
    $this->post(route('dev.login'), ['user_id' => 9999])
        ->assertNotFound();
});

// Livewire component tests
it('renders the login panel with users', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    Livewire::test(LoginPanel::class)
        ->assertSee($user->name);
});

it('logs in via the livewire component as a student', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    Livewire::test(LoginPanel::class)
        ->set('userId', $user->id)
        ->call('login')
        ->assertRedirect(route('portal.tickets.index'));

    expect(auth()->user()->id)->toBe($user->id);
});

it('logs in via the livewire component as an admin', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    Livewire::test(LoginPanel::class)
        ->set('userId', $user->id)
        ->call('login')
        ->assertRedirect('/admin');
});
