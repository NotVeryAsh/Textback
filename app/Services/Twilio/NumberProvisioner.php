<?php

declare(strict_types=1);

namespace App\Services\Twilio;

use App\Models\Account;
use App\Models\PhoneNumber;
use RuntimeException;

class NumberProvisioner
{
    public function __construct(private TwilioClientFactory $factory) {}

    public function configured(): bool
    {
        return $this->factory->configured();
    }

    /**
     * Search for, purchase, and configure a fresh local Twilio number, then
     * point its voice + SMS webhooks at this app. Requires Twilio creds.
     */
    public function provision(Account $account, ?string $areaCode = null): PhoneNumber
    {
        $client = $this->factory->make();

        $searchParams = ['smsEnabled' => true, 'voiceEnabled' => true];
        if ($areaCode !== null && $areaCode !== '') {
            $searchParams['areaCode'] = $areaCode;
        }

        $available = $client->availablePhoneNumbers('US')
            ->local
            ->read($searchParams, 1);

        if ($available === []) {
            throw new RuntimeException('No matching Twilio numbers are available. Try a different area code.');
        }

        $incoming = $client->incomingPhoneNumbers->create([
            'phoneNumber' => $available[0]->phoneNumber,
            'voiceUrl' => route('webhooks.twilio.voice'),
            'voiceMethod' => 'POST',
            'smsUrl' => route('webhooks.twilio.sms'),
            'smsMethod' => 'POST',
            'statusCallback' => route('webhooks.twilio.status'),
        ]);

        return $this->persist($account, $incoming->phoneNumber, $incoming->sid, [
            'voice' => true,
            'sms' => true,
        ]);
    }

    /**
     * Concierge / not-configured fallback: attach a number the operator
     * already controls (entered by hand during onboarding).
     */
    public function attachManual(Account $account, string $e164): PhoneNumber
    {
        return $this->persist($account, $e164, null, null);
    }

    /**
     * @param  array<string, bool>|null  $capabilities
     */
    private function persist(Account $account, string $e164, ?string $sid, ?array $capabilities): PhoneNumber
    {
        $account->update([
            'twilio_number' => $e164,
            'twilio_number_sid' => $sid,
        ]);

        return $account->phoneNumbers()->updateOrCreate(
            ['e164' => $e164],
            ['twilio_sid' => $sid, 'capabilities' => $capabilities, 'status' => 'active'],
        );
    }
}
