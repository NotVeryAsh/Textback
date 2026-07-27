<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ForwardInboundSms;
use App\Models\Account;
use App\Models\Message;
use App\Services\Messaging\SmsSender;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TwilioSmsController extends Controller
{
    private const OPT_OUT_KEYWORDS = ['STOP', 'STOPALL', 'UNSUBSCRIBE', 'CANCEL', 'END', 'QUIT'];

    /**
     * Inbound SMS (a lead replying). Record it, mark the lead as replied, and
     * notify the operator. Opt-out keywords mark the lead as ignored.
     */
    public function incoming(Request $request, SmsSender $sender): Response
    {
        $account = Account::where('twilio_number', $request->input('To'))->first();

        if ($account === null) {
            return $this->emptyTwiml();
        }

        $from = Phone::normalize((string) $request->input('From')) ?? (string) $request->input('From');
        $body = (string) $request->input('Body');

        $lead = $account->leads()->where('phone', $from)->first();

        $isOptOut = in_array(strtoupper(trim($body)), self::OPT_OUT_KEYWORDS, true);

        if ($lead !== null) {
            $lead->update([
                'status' => $isOptOut ? LeadStatus::Ignored : LeadStatus::Replied,
            ]);
        }

        $sender->recordInbound($account, $from, $body, $lead, $request->input('MessageSid'));

        if ($lead !== null && ! $isOptOut) {
            ForwardInboundSms::dispatch($lead->id, $body);
        }

        return $this->emptyTwiml();
    }

    /**
     * Delivery status callback for outbound messages.
     */
    public function status(Request $request): Response
    {
        $sid = $request->input('MessageSid');
        $status = $request->input('MessageStatus');

        if (is_string($sid) && is_string($status)) {
            Message::where('twilio_sid', $sid)->update(['status' => $status]);
        }

        return response('', 204);
    }

    private function emptyTwiml(): Response
    {
        return response('<Response></Response>', 200)->header('Content-Type', 'text/xml');
    }
}
