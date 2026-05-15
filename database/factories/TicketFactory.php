<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Office;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'requester_id' => User::factory(),
            'office_id' => Office::factory(),
            'service_type_id' => ServiceType::factory(),
            'assigned_to' => null,
            'status' => TicketStatus::Pending,
            'priority' => TicketPriority::Normal,
            'subject' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'custom_fields' => null,
        ];
    }

    public function assigned(?User $assignee = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::Assigned,
            'assigned_to' => $assignee?->id ?? User::factory(),
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::Resolved,
            'resolved_at' => now(),
        ]);
    }
}
