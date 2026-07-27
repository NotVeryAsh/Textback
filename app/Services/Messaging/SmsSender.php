<?php

declare(strict_types=1);

namespace App\Services\Messaging;

use App\Enums\MessageDirection;
use App\Jobs\SendSms;
use App\Models\Account;
use App\Models\Lead;
use App\Models\Message;

class SmsSender
{
    /**
     * Record an outbound SMS and queue it for delivery. The webhook/UI stays
     * fast because the actual Twilio call happens in the SendSms job.
     */
    public function send(Account $account, string $to, string $body, ?Lead $lead = null, ?string $mediaUrl = null): Message
    {
        $message = $account->messages()->create([
            'lead_id' => $lead?->id,
            'direction' => MessageDirection::Outbound,
            'from' => $account->twilio_number,
            'to' => $to,
            'body' => $body,
            'media_url' => $mediaUrl,
            'status' => 'queued',
        ]);

        $account->currentUsage()->increment('sms_out');

        SendSms::dispatch($message->id);

        return $message;
    }

    /**
     * Record an inbound SMS (already received via webhook).
     */
    public function recordInbound(Account $account, string $from, string $body, ?Lead $lead = null, ?string $twilioSid = null): Message
    {
        $account->currentUsage()->increment('sms_in');

        return $account->messages()->create([
            'lead_id' => $lead?->id,
            'direction' => MessageDirection::Inbound,
            'from' => $from,
            'to' => $account->twilio_number,
            'body' => $body,
            'twilio_sid' => $twilioSid,
            'status' => 'received',
            'sent_at' => now(),
        ]);
    }
}
