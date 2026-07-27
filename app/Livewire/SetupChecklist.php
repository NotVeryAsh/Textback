<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Services\Billing\BillingService;
use App\Services\Messaging\SmsSender;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Progressive-onboarding checklist shown on the dashboard. Holds the steps
 * deferred out of the slim wizard (verify phone, review link, first lead,
 * billing). Phone verification is inline here, off the signup critical path.
 */
class SetupChecklist extends Component
{
    public bool $verifying = false;

    public string $cell_code = '';

    public ?string $devCode = null;

    public function sendCode(SmsSender $sms): void
    {
        $account = auth()->user()->account;

        if ($account->operator_cell === null) {
            return;
        }

        $code = (string) random_int(100000, 999999);
        Cache::put($this->codeCacheKey(), $code, now()->addMinutes(10));

        $sms->send($account, $account->operator_cell, "Your Textback verification code is {$code}");

        $this->verifying = true;
        $this->devCode = config('services.twilio.sid') ? null : $code;
    }

    public function verify(): void
    {
        if (! config('textback.require_real_otp')) {
            if (! preg_match('/^\d{6}$/', $this->cell_code)) {
                $this->addError('cell_code', 'Enter a 6-digit code.');

                return;
            }
        } else {
            $expected = Cache::get($this->codeCacheKey());

            if ($expected === null || $this->cell_code !== $expected) {
                $this->addError('cell_code', 'That code is not correct or has expired.');

                return;
            }
        }

        auth()->user()->account->update(['operator_cell_verified_at' => now()]);
        Cache::forget($this->codeCacheKey());
        $this->reset('verifying', 'cell_code', 'devCode');
    }

    public function dismiss(): void
    {
        auth()->user()->account->update(['setup_dismissed' => true]);
    }

    private function codeCacheKey(): string
    {
        return 'textback:otp:account:'.auth()->user()->account->id;
    }

    public function render(BillingService $billing): View
    {
        $account = auth()->user()->account;

        $tasks = [
            [
                'key' => 'verify',
                'label' => 'Verify your phone',
                'done' => $account->operatorCellVerified(),
            ],
            [
                'key' => 'review',
                'label' => 'Add your Google review link',
                'done' => $account->hasReviewLink(),
                'route' => route('settings'),
            ],
            [
                'key' => 'lead',
                'label' => 'Capture your first lead',
                'done' => $account->hasCapturedLead(),
            ],
            [
                'key' => 'billing',
                'label' => 'Start your free trial',
                'done' => $billing->subscribed($account),
                'route' => route('billing'),
            ],
        ];

        $doneCount = count(array_filter($tasks, fn ($t) => $t['done']));

        return view('livewire.setup-checklist', [
            'account' => $account,
            'tasks' => $tasks,
            'doneCount' => $doneCount,
            'total' => count($tasks),
            'allDone' => $doneCount === count($tasks),
        ]);
    }
}
