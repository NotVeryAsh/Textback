<?php

declare(strict_types=1);

namespace App\Enums;

enum TemplateKind: string
{
    case MissedCall = 'missed_call';
    case Review = 'review';
    case Nurture = 'nurture';

    public function label(): string
    {
        return match ($this) {
            self::MissedCall => 'Missed-call text-back',
            self::Review => 'Review request',
            self::Nurture => 'Follow-up / nurture',
        };
    }

    /**
     * Nurture (pillar 3) is defined but not sent in v1.
     */
    public function activeInV1(): bool
    {
        return $this !== self::Nurture;
    }
}
