<?php

namespace Database\Factories;

use App\Enums\OnboardingStatus;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'google_id' => null,
            'avatar' => null,
            'onboarding_completed_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withGoogle(): static
    {
        return $this->state(fn (array $attributes) => [
            'google_id' => fake()->uuid(),
            'avatar' => fake()->imageUrl(),
        ]);
    }

    public function pendingEmployee(Office $office): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_status' => OnboardingStatus::PendingEmployee,
            'pending_office_id' => $office->id,
            'onboarding_completed_at' => now(),
        ]);
    }

    public function rejectedEmployee(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_status' => OnboardingStatus::Rejected,
            'pending_office_id' => null,
            'onboarding_completed_at' => now(),
        ]);
    }

    public function needsOnboarding(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_completed_at' => null,
        ]);
    }
}
