<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadSource: string
{
    case MissedCall = 'missed_call';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::MissedCall => 'Missed call',
            self::Manual => 'Added manually',
        };
    }
}
