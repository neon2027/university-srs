<?php

namespace App\Models;

use App\Enums\CreditType;
use Database\Factories\ForwardingLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ticket_id', 'from_office_id', 'to_office_id',
    'forwarded_by', 'accepted_by', 'credit_type',
    'note', 'forwarded_at', 'responded_at',
])]
class ForwardingLog extends Model
{
    /** @use HasFactory<ForwardingLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'credit_type' => CreditType::class,
            'forwarded_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function fromOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'from_office_id');
    }

    public function toOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'to_office_id');
    }

    public function forwardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'forwarded_by');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }
}
