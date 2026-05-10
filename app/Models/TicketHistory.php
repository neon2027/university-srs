<?php

namespace App\Models;

use App\Enums\EventType;
use App\Enums\TicketStatus;
use Database\Factories\TicketHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_id', 'actor_id', 'event_type', 'from_status', 'to_status', 'note', 'meta'])]
class TicketHistory extends Model
{
    /** @use HasFactory<TicketHistoryFactory> */
    use HasFactory;

    protected $table = 'ticket_history';

    protected function casts(): array
    {
        return [
            'event_type' => EventType::class,
            'from_status' => TicketStatus::class,
            'to_status' => TicketStatus::class,
            'meta' => 'array',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
