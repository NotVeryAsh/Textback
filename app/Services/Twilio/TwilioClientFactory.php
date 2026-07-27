<?php

declare(strict_types=1);

namespace App\Services\Twilio;

use RuntimeException;
use Twilio\Rest\Client;

class TwilioClientFactory
{
    private ?Client $client = null;

    /**
     * Whether Twilio credentials are present. When false, the app runs in
     * "not configured" mode: onboarding and UI work, but no real calls or
     * texts are placed (jobs record a skipped status instead of failing).
     */
    public function configured(): bool
    {
        return filled(config('services.twilio.sid'))
            && filled(config('services.twilio.token'));
    }

    public function make(): Client
    {
        if (! $this->configured()) {
            throw new RuntimeException('Twilio is not configured. Set TWILIO_ACCOUNT_SID and TWILIO_AUTH_TOKEN.');
        }

        return $this->client ??= new Client(
            (string) config('services.twilio.sid'),
            (string) config('services.twilio.token'),
        );
    }
}
