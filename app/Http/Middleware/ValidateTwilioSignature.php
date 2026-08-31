<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Account;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Security\RequestValidator;

class ValidateTwilioSignature
{
    /**
     * Verify the X-Twilio-Signature header so only Twilio can hit our webhooks.
     * Signed with the auth token of whichever Twilio account owns the number
     * (per-client accounts have their own tokens); falls back to the global
     * token. Skipped when signature validation is disabled (local sim) or no
     * token is configured.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->tokenFor($request) ?? config('services.twilio.token');

        if (! config('services.twilio.validate_signature') || blank($token)) {
            return $next($request);
        }

        $validator = new RequestValidator((string) $token);

        $signature = $request->header('X-Twilio-Signature', '');
        $valid = $validator->validate($signature, $request->fullUrl(), $request->post());

        abort_unless($valid, 403, 'Invalid Twilio signature.');

        return $next($request);
    }

    /**
     * The per-account auth token for the number this webhook concerns, if that
     * account runs in its own Twilio account. Inbound voice/SMS carry our
     * number in To; outbound status callbacks carry it in From.
     */
    private function tokenFor(Request $request): ?string
    {
        $numbers = array_filter([$request->post('To'), $request->post('From')]);

        if ($numbers === []) {
            return null;
        }

        $account = Account::whereIn('twilio_number', $numbers)
            ->whereNotNull('twilio_account_sid')
            ->first();

        return $account?->usesOwnTwilio() ? $account->twilio_auth_token : null;
    }
}
