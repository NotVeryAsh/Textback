<?php

declare(strict_types=1);

namespace App\Services\Twilio;

use App\Models\Account;
use RuntimeException;
use Twilio\Rest\Client;

class TwilioClientFactory
{
    /** @var array<string, Client> */
    private array $clients = [];

    /**
     * Whether Twilio credentials are present. When false, the app runs in
     * "not configured" mode: onboarding and UI work, but no real calls or
     * texts are placed (jobs record a skipped status instead of failing).
     *
     * Pass an account to also count that account's own credentials
     * (per-client Twilio accounts fall back to the global env creds).
     */
    public function configured(?Account $account = null): bool
    {
        if ($account?->usesOwnTwilio()) {
            return true;
        }

        return filled(config('services.twilio.sid'))
            && filled(config('services.twilio.token'));
    }

    /**
     * A Twilio client for the given account: the account's own credentials
     * when set, otherwise the global platform credentials.
     */
    public function for(?Account $account): Client
    {
        if ($account?->usesOwnTwilio()) {
            $sid = (string) $account->twilio_account_sid;

            return $this->clients[$sid] ??= new Client($sid, (string) $account->twilio_auth_token);
        }

        return $this->make();
    }

    public function make(): Client
    {
        if (! $this->configured()) {
            throw new RuntimeException('Twilio is not configured. Set TWILIO_ACCOUNT_SID and TWILIO_AUTH_TOKEN.');
        }

        $sid = (string) config('services.twilio.sid');

        return $this->clients[$sid] ??= new Client($sid, (string) config('services.twilio.token'));
    }
}
