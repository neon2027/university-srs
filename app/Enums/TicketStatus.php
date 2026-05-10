<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case OnHold = 'on_hold';
    case Forwarded = 'forwarded';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Assigned => 'Assigned',
            self::InProgress => 'In Progress',
            self::OnHold => 'On Hold',
            self::Forwarded => 'Forwarded',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Assigned, self::InProgress => 'info',
            self::Forwarded => 'primary',
            self::Resolved, self::Closed => 'success',
            self::OnHold => 'gray',
            self::Cancelled => 'danger',
        };
    }
}
