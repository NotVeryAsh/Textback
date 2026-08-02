<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Services\Twilio\TwilioClientFactory;
use App\Support\Phone;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ForwardInboundSms implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $leadId, public string $body) {}

    /**
     * Relay the lead's message to the operator's own phone (from the Textback
     * number) so they can simply reply from their phone. Their reply comes back
     * to the Textback number and is routed to this lead. Best-effort only.
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

        // Prefix with who it's from so the operator knows who they're replying to.
        $notice = sprintf('%s (%s): %s', $lead->displayName(), Phone::pretty($lead->phone), $this->body);

        if (! $factory->configured()) {
            Log::info('Inbound reply forward skipped (Twilio not configured)', [
                'operator' => $account->operator_cell,
                'notice' => $notice,
            ]);

            return;
        }

        $messagingServiceSid = config('services.twilio.messaging_service_sid');

        $factory->make()->messages->create($account->operator_cell, array_filter([
            'messagingServiceSid' => $messagingServiceSid ?: null,
            'from' => $messagingServiceSid ? null : $account->twilio_number,
            'body' => $notice,
        ], fn ($value) => $value !== null));
    }
}
