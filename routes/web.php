<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Webhooks\TwilioSmsController;
use App\Http\Controllers\Webhooks\TwilioVoiceController;
use App\Livewire\Billing\Billing;
use App\Livewire\Dashboard;
use App\Livewire\Leads\LeadsIndex;
use App\Livewire\Onboarding\Wizard;
use App\Livewire\Reviews\ReviewsIndex;
use App\Livewire\Sequences\FollowUps;
use App\Livewire\Settings\Settings;
use Illuminate\Support\Facades\Route;

Route::view('/', 'marketing.home')->name('home');

// Public legal pages (required for A2P 10DLC registration + general use).
Route::view('/privacy', 'legal.privacy')->name('privacy');
Route::view('/terms', 'legal.terms')->name('terms');

// Google sign-in (Socialite). Guarded: 404 when creds are absent.
Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

/*
| Authenticated app
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Onboarding + billing are reachable before the account is fully set up.
    Route::get('/onboarding', Wizard::class)->name('onboarding');
    Route::get('/billing', Billing::class)->name('billing');

    // Core app, gated until onboarding is complete.
    Route::middleware('account.onboarded')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
        Route::get('/leads', LeadsIndex::class)->name('leads');
        Route::get('/reviews', ReviewsIndex::class)->name('reviews');
        Route::get('/follow-ups', FollowUps::class)->name('follow-ups');
        Route::get('/settings', Settings::class)->name('settings');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
| Twilio webhooks (no auth; signature-verified in production)
*/
Route::middleware('twilio.signature')->prefix('webhooks/twilio')->name('webhooks.twilio.')->group(function () {
    Route::post('voice', [TwilioVoiceController::class, 'incoming'])->name('voice');
    Route::post('after-dial', [TwilioVoiceController::class, 'afterDial'])->name('after-dial');
    Route::post('whisper/{account}', [TwilioVoiceController::class, 'whisper'])->name('whisper');
    Route::post('sms', [TwilioSmsController::class, 'incoming'])->name('sms');
    Route::post('status', [TwilioSmsController::class, 'status'])->name('status');
});

require __DIR__.'/auth.php';
