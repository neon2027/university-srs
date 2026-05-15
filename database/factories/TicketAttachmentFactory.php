<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketAttachment>
 */
class TicketAttachmentFactory extends Factory
{
    public function definition(): array
    {
        $size = fake()->numberBetween(100_000, 10_000_000);

        return [
            'ticket_id' => Ticket::factory(),
            'ticket_message_id' => null,
            'uploader_id' => User::factory(),
            'disk' => 'local',
            'path' => 'attachments/'.fake()->uuid().'.pdf',
            'original_filename' => fake()->word().'.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => $size,
            'compressed_size_bytes' => (int) ($size * 0.4),
        ];
    }
}
