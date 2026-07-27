<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Delivery channel for a message / sequence step. v1 sends SMS only; MMS
 * (with media such as a PDF invoice) is a future paid-tier capability. The
 * enum + the media_url columns exist now so that expansion is data-driven.
 */
enum MessageChannel: string
{
    case Sms = 'sms';
    case Mms = 'mms';
}
