<?php

declare(strict_types=1);

namespace App\Enums;

enum CallerIdMode: string
{
    /** Operator sees the lead's real number when the call rings. */
    case Lead = 'lead';

    /** Operator sees the Textback number (clearly a business lead). */
    case Textback = 'textback';

    /** Operator hears a short whisper prompt before the call connects. */
    case Whisper = 'whisper';

    public function label(): string
    {
        return match ($this) {
            self::Lead => "Show the caller's number",
            self::Textback => 'Show my Textback number',
            self::Whisper => 'Announce with a whisper',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Lead => 'Your phone shows who is calling, so you can call back directly.',
            self::Textback => 'Every call shows your Textback number, so you know it is a business lead.',
            self::Whisper => 'You hear "call from your Textback line, press any key to accept" before connecting.',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
