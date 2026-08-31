<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Services\Twilio\TwilioClientFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ForwardInboundSms implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $leadId, public string $body) {}

    /**
     * Alert the operator (by SMS to their cell) that a lead replied, so they can
     * open the app and respond there. This is a notification only - the operator
     * replies in the dashboard (correct, concurrency-safe routing), not by
     * texting back on their personal phone.
     */
    public function handle(TwilioClientFactory $factory): void
    {
        $lead = Lead::with('account')->find($this->leadId);

        if ($lead === null) {
            return;
        }

        $account = $lead->account;

        if ($account->operator_cell === null) {
            return;
        }

        $notice = sprintf(
            'Textback: %s replied "%s". Open your dashboard to respond.',
            $lead->displayName(),
            Str::limit($this->body, 120),
        );

        if (! $factory->configured($account)) {
            Log::info('Inbound reply forward skipped (Twilio not configured)', [
                'operator' => $account->operator_cell,
                'notice' => $notice,
            ]);

            return;
        }

        $messagingServiceSid = $account->messagingServiceSid();

        $factory->for($account)->messages->create($account->operator_cell, array_filter([
            'messagingServiceSid' => $messagingServiceSid,
            'from' => $messagingServiceSid ? null : $account->twilio_number,
            'body' => $notice,
        ], fn ($value) => $value !== null));
    }
}
