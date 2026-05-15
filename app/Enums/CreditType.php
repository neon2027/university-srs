<?php

namespace App\Enums;

enum CreditType: string
{
    case AcceptCredit = 'accept_credit';
    case ReferenceOnly = 'reference_only';
}
