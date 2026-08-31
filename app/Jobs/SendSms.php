<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Message;
use App\Services\Twilio\TwilioClientFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendSms implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(public int $messageId) {}

    public function handle(TwilioClientFactory $factory): void
    {
        $message = Message::with('account')->find($this->messageId);

        if ($message === null) {
            return;
        }

        $account = $message->account;

        // No Twilio creds: record a clear status instead of failing so the
        // app is fully usable in local/concierge mode.
        if (! $factory->configured($account)) {
            $message->update(['status' => 'skipped_no_twilio']);
            Log::info('SMS skipped (Twilio not configured)', [
                'to' => $message->to,
                'body' => $message->body,
            ]);

            return;
        }

        try {
            // Account's own messaging service (per-client Twilio account)
            // falls back to the global platform one.
            $messagingServiceSid = $account?->messagingServiceSid();

            $params = array_filter([
                'messagingServiceSid' => $messagingServiceSid,
                'from' => $messagingServiceSid ? null : $message->from,
                'body' => $message->body,
                // Future MMS: when a media_url is set, Twilio sends it as MMS.
                'mediaUrl' => $message->media_url ? [$message->media_url] : null,
                'statusCallback' => route('webhooks.twilio.status'),
            ], fn ($value) => $value !== null);

            $sent = $factory->for($account)->messages->create($message->to, $params);

            $message->update([
                'twilio_sid' => $sent->sid,
                'status' => 'sent',
                'sent_at' => now(),
                'error' => null,
            ]);
        } catch (Throwable $e) {
            $message->update(['status' => 'failed', 'error' => $e->getMessage()]);

            throw $e;
        }
    }
}
