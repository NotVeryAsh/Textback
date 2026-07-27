<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleController extends Controller
{
    public function redirect()
    {
        abort_unless($this->configured(), 404);

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        abort_unless($this->configured(), 404);

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return redirect()->route('login')->withErrors([
                'email' => 'Google sign-in failed. Please try again or use email.',
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName() ?: 'there',
                'password' => bcrypt(Str::random(40)),
                'email_verified_at' => now(),
            ],
        );

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard'));
    }

    private function configured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'));
    }
}
