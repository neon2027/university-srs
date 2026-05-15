<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketMessage>
 */
class TicketMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'sender_id' => User::factory(),
            'body' => fake()->paragraph(),
            'is_internal_note' => false,
            'is_canned_response' => false,
            'seen_at' => null,
        ];
    }

    public function internalNote(): static
    {
        return $this->state(fn (array $attributes) => ['is_internal_note' => true]);
    }

    public function guestReply(string $guestName = 'Test User'): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_id' => null,
            'guest_name' => $guestName,
        ]);
    }

    public function requestingAttachment(): static
    {
        return $this->state(fn (array $attributes) => ['requests_attachment' => true]);
    }
}
