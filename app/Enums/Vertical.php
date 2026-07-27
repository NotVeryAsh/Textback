<?php

declare(strict_types=1);

namespace App\Enums;

enum Vertical: string
{
    case Realtor = 'realtor';
    case Contractor = 'contractor';
    case Freelancer = 'freelancer';

    public function label(): string
    {
        return (string) config("textback.verticals.{$this->value}.label", ucfirst($this->value));
    }

    /**
     * The word this vertical uses for the person who calls (lead/customer/client).
     */
    public function leadNoun(): string
    {
        return (string) config("textback.verticals.{$this->value}.lead_noun", 'lead');
    }

    /**
     * Default template bodies for this vertical, keyed by TemplateKind value.
     *
     * @return array<string, string>
     */
    public function defaultTemplates(): array
    {
        return (array) config("textback.verticals.{$this->value}.templates", []);
    }

    /**
     * The default pillar-3 sequence definition for this vertical.
     *
     * @return array{kind: string, name: string, trigger: string, steps: array<int, array{delay_minutes: int, body: string}>}|null
     */
    public function defaultSequence(): ?array
    {
        return config("textback.sequences.{$this->value}");
    }

    /**
     * @return array<string, string> value => label, for select inputs.
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
