<?php

namespace App\Enums;

enum OnboardingStatus: string
{
    case PendingEmployee = 'pending_employee';
    case Rejected = 'rejected';
}
