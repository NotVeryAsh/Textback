<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Security\RequestValidator;

class ValidateTwilioSignature
{
    /**
     * Verify the X-Twilio-Signature header so only Twilio can hit our webhooks.
     * Skipped when signature validation is disabled (local sim) or no auth
     * token is configured.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('services.twilio.token');

        if (! config('services.twilio.validate_signature') || blank($token)) {
            return $next($request);
        }

        $validator = new RequestValidator((string) $token);

        $signature = $request->header('X-Twilio-Signature', '');
        $valid = $validator->validate($signature, $request->fullUrl(), $request->post());

        abort_unless($valid, 403, 'Invalid Twilio signature.');

        return $next($request);
    }
}
