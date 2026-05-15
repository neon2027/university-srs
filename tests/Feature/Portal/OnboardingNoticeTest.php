<?php

use App\Livewire\Portal\OnboardingNotice;
use App\Models\User;
use Livewire\Livewire;

test('onboarding notice renders when user has no notice status', function () {
    $user = User::factory()->create([
        'onboarding_status' => null,
        'pending_office_id' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(OnboardingNotice::class)
        ->assertOk()
        ->assertDontSee('pending verification')
        ->assertDontSee('employee verification request was not approved');
});
