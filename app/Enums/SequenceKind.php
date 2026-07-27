<?php

declare(strict_types=1);

namespace App\Enums;

enum SequenceKind: string
{
    /** Chase an unpaid invoice (contractors / freelancers). */
    case InvoiceReminder = 'invoice_reminder';

    /** Keep a cold lead or past client warm (realtors). */
    case Nurture = 'nurture';

    public function label(): string
    {
        return match ($this) {
            self::InvoiceReminder => 'Invoice reminders',
            self::Nurture => 'Follow-ups',
        };
    }

    /**
     * Does this sequence chase money (needs invoice context) or just nurture?
     */
    public function needsInvoiceContext(): bool
    {
        return $this === self::InvoiceReminder;
    }
}
