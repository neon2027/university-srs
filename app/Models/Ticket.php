<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'ulid', 'requester_id', 'office_id', 'service_type_id', 'assigned_to',
    'status', 'priority', 'subject', 'description', 'custom_fields',
    'resolved_at', 'closed_at',
])]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'custom_fields' => 'array',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Ticket $ticket) {
            $ticket->ulid ??= $ticket->generateTicketNumber();
        });
    }

    private function generateTicketNumber(): string
    {
        $office = $this->office()->first();
        $acronym = $this->officeAcronym($office?->name ?? 'Office');
        $prefix = "{$acronym}-T-".now()->format('y');

        $latestNumber = static::withTrashed()
            ->where('ulid', 'like', "{$prefix}-%")
            ->pluck('ulid')
            ->map(function (string $ticketNumber): int {
                $sequence = str($ticketNumber)->afterLast('-')->toString();

                return ctype_digit($sequence) ? (int) $sequence : 0;
            })
            ->max() ?? 0;

        return sprintf('%s-%04d', $prefix, $latestNumber + 1);
    }

    private function officeAcronym(string $officeName): string
    {
        preg_match_all('/[A-Za-z0-9]+/', $officeName, $matches);

        $acronym = collect($matches[0])
            ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');

        return str($acronym ?: 'OFFICE')->limit(12, '')->toString();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(TicketHistory::class)->orderBy('created_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function rating(): HasOne
    {
        return $this->hasOne(TicketRating::class);
    }
}
