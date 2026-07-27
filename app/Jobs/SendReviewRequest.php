<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\TemplateKind;
use App\Models\ReviewRequest;
use App\Services\Messaging\SmsSender;
use App\Services\Templates\TemplateRenderer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendReviewRequest implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $reviewRequestId) {}

    public function handle(TemplateRenderer $renderer, SmsSender $sms): void
    {
        $reviewRequest = ReviewRequest::with('account')->find($this->reviewRequestId);

        if ($reviewRequest === null || $reviewRequest->status === 'sent') {
            return;
        }

        $account = $reviewRequest->account;

        $body = $renderer->render(
            $account->templateBody(TemplateKind::Review),
            $account,
        );

        $message = $sms->send($account, $reviewRequest->phone, $body);

        $reviewRequest->update([
            'status' => 'sent',
            'sent_at' => now(),
            'message_id' => $message->id,
        ]);
    }
}
