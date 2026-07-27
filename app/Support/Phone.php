<?php

declare(strict_types=1);

namespace App\Support;

use Propaganistas\LaravelPhone\PhoneNumber;
use Throwable;

class Phone
{
    /**
     * Normalize a raw phone string to E.164 (e.g. +15551234567).
     * Returns null when the input cannot be parsed as a valid number.
     */
    public static function normalize(?string $input, string $defaultCountry = 'US'): ?string
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        try {
            return (new PhoneNumber($input, $defaultCountry))->formatE164();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * A human-friendly national format for display, falling back to the raw input.
     */
    public static function pretty(?string $input, string $defaultCountry = 'US'): string
    {
        if ($input === null || trim($input) === '') {
            return '';
        }

        try {
            return (new PhoneNumber($input, $defaultCountry))->formatNational();
        } catch (Throwable) {
            return $input;
        }
    }
}
