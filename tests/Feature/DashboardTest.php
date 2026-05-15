<?php

use App\Models\User;

test('guests are redirected to Google sign-in', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('auth.google'));
});

test('authenticated users are redirected from the dashboard to the portal', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('portal.tickets.index'));
});
