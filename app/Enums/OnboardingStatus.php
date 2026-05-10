<?php

namespace App\Enums;

enum OnboardingStatus: string
{
    case PendingEmployee = 'pending_employee';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PendingEmployee => 'Pending Verification',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PendingEmployee => 'warning',
            self::Rejected => 'danger',
        };
    }
}
