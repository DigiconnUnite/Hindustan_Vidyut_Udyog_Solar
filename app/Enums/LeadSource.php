<?php

namespace App\Enums;

enum LeadSource: string
{
    case Website = 'website';
    case Phone = 'phone';
    case WalkIn = 'walk_in';
    case Referral = 'referral';
    case Other = 'other';
}
