<?php

namespace Database\Factories;

use App\Models\ForwardingLog;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForwardingLog>
 */
class ForwardingLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'from_office_id' => Office::factory(),
            'to_office_id' => Office::factory(),
            'forwarded_by' => User::factory(),
            'accepted_by' => null,
            'credit_type' => null,
            'note' => fake()->optional()->sentence(),
            'forwarded_at' => now(),
            'responded_at' => null,
        ];
    }
}
