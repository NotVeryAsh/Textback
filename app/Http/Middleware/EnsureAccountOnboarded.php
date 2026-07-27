<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountOnboarded
{
    /**
     * Send users to the onboarding wizard until their account is fully set up.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->user()?->account;

        if ($account === null || ! $account->isOnboarded()) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
