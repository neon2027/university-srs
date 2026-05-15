<?php

namespace Database\Factories;

use App\Enums\EventType;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketHistory>
 */
class TicketHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'actor_id' => User::factory(),
            'event_type' => EventType::Created,
            'from_status' => null,
            'to_status' => null,
            'note' => null,
        ];
    }
}
