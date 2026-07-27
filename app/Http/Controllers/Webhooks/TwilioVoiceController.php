<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Leads\MissedCallHandler;
use App\Services\Twilio\TwimlBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TwilioVoiceController extends Controller
{
    /**
     * Inbound call to a Textback number: forward it to the operator's real phone.
     */
    public function incoming(Request $request, TwimlBuilder $twiml): Response
    {
        $account = $this->resolveAccount($request->input('To'));

        if ($account === null || $account->operator_cell === null) {
            return $this->xml($twiml->empty());
        }

        $xml = $twiml->dialOperator(
            $account,
            (string) $request->input('From'),
            route('webhooks.twilio.after-dial'),
            route('webhooks.twilio.whisper', ['account' => $account->id]),
        );

        return $this->xml($xml);
    }

    /**
     * Result of the forward attempt. If the operator did not pick up, text
     * the caller back.
     */
    public function afterDial(Request $request, MissedCallHandler $handler, TwimlBuilder $twiml): Response
    {
        if ($request->input('DialCallStatus') !== 'completed') {
            $account = $this->resolveAccount($request->input('To'));

            if ($account !== null) {
                $handler->handle($account, (string) $request->input('From'));
            }
        }

        return $this->xml($twiml->empty());
    }

    /**
     * Whisper played to the operator only, before the call connects.
     */
    public function whisper(Account $account, TwimlBuilder $twiml): Response
    {
        return $this->xml($twiml->whisper($account));
    }

    private function resolveAccount(mixed $twilioNumber): ?Account
    {
        if (! is_string($twilioNumber) || $twilioNumber === '') {
            return null;
        }

        return Account::where('twilio_number', $twilioNumber)->first();
    }

    private function xml(string $body): Response
    {
        return response($body, 200)->header('Content-Type', 'text/xml');
    }
}
