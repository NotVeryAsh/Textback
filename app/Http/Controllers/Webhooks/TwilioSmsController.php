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
     * Inbound SMS to the Textback number. Two directions of the relay:
     *  - from the operator's own phone -> relay to the lead they're talking to
     *  - from a lead -> record it, mark replied, and relay to the operator's phone
     * Everything is recorded so the conversation is also visible in the app.
     */
    public function incoming(Request $request, SmsSender $sender): Response
    {
        $account = Account::where('twilio_number', $request->input('To'))->first();

        if ($account === null) {
            return $this->emptyTwiml();
        }

        $from = Phone::normalize((string) $request->input('From')) ?? (string) $request->input('From');
        $body = (string) $request->input('Body');

        // Operator replying from their own phone -> send it on to the active lead.
        if ($account->operator_cell !== null && $from === Phone::normalize($account->operator_cell)) {
            $lead = $account->activeLead();

            if ($lead !== null && trim($body) !== '') {
                $sender->send($account, $lead->phone, $body, $lead);
                $lead->update(['last_contacted_at' => now()]);
            }

            return $this->emptyTwiml();
        }

        // Otherwise it's the caller (lead) replying.
        $lead = $account->leads()->where('phone', $from)->first();
        $isOptOut = in_array(strtoupper(trim($body)), self::OPT_OUT_KEYWORDS, true);

        if ($lead !== null) {
            $lead->update([
                'status' => $isOptOut ? LeadStatus::Ignored : LeadStatus::Replied,
            ]);
        }

        $sender->recordInbound($account, $from, $body, $lead, $request->input('MessageSid'));

        // Relay the lead's message to the operator's phone so they can reply there.
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
