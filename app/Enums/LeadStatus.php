<?php

declare(strict_types=1);

namespace App\Enums;

enum LeadStatus: string
{
    case TextedBack = 'texted_back';
    case Replied = 'replied';
    case Converted = 'converted';
    case Closed = 'closed';
    case Ignored = 'ignored';

    public function label(): string
    {
        return match ($this) {
            self::TextedBack => 'Texted back',
            self::Replied => 'Replied',
            self::Converted => 'Converted',
            self::Closed => 'Closed',
            self::Ignored => 'Ignored',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TextedBack => 'blue',
            self::Replied => 'amber',
            self::Converted => 'green',
            self::Closed => 'gray',
            self::Ignored => 'gray',
        };
    }
}
