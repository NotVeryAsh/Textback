<?php

declare(strict_types=1);

namespace App\Services\Twilio;

use App\Enums\CallerIdMode;
use App\Models\Account;
use Twilio\TwiML\VoiceResponse;

class TwimlBuilder
{
    /**
     * Build the TwiML that forwards an inbound call to the operator's real
     * phone. Caller ID and whisper behaviour depend on the account setting.
     */
    public function dialOperator(
        Account $account,
        string $callerNumber,
        string $actionUrl,
        ?string $whisperUrl = null,
    ): string {
        $response = new VoiceResponse;

        $mode = $account->caller_id_mode ?? CallerIdMode::Lead;

        $callerId = match ($mode) {
            CallerIdMode::Textback => $account->twilio_number,
            default => $callerNumber, // Lead + Whisper both show the caller.
        };

        $dial = $response->dial('', array_filter([
            'answerOnBridge' => true,
            'timeout' => (int) config('services.twilio.dial_timeout', 18),
            'callerId' => $callerId,
            'action' => $actionUrl,
            'method' => 'POST',
        ], fn ($value) => $value !== null));

        $numberAttributes = [];

        if ($mode === CallerIdMode::Whisper && $whisperUrl !== null) {
            $numberAttributes['url'] = $whisperUrl;
        }

        $dial->number($account->operator_cell, $numberAttributes);

        return (string) $response;
    }

    /**
     * TwiML played to the operator (only) before the call connects.
     */
    public function whisper(Account $account): string
    {
        $response = new VoiceResponse;
        $response->say(
            'Call from your Textback line for '.$account->business_name.'. Connecting now.',
            ['voice' => 'alice']
        );

        return (string) $response;
    }

    /**
     * An empty acknowledgement (200) for callbacks that need no further action.
     */
    public function empty(): string
    {
        return (string) new VoiceResponse;
    }
}
