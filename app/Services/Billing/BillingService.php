<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\Account;
use Illuminate\Support\Carbon;
use Throwable;

class BillingService
{
    public function stripeConfigured(): bool
    {
        return filled(config('cashier.secret'));
    }

    public function subscribed(Account $account): bool
    {
        return $account->user->subscribed('default');
    }

    /**
     * On trial when the pre-subscription generic trial is active, or the
     * Stripe subscription itself is still in its trial window.
     */
    public function onTrial(Account $account): bool
    {
        $user = $account->user;

        if ($user->subscription('default')?->onTrial()) {
            return true;
        }

        return $user->onGenericTrial();
    }

    public function trialEndsAt(Account $account): ?Carbon
    {
        $user = $account->user;

        return $user->subscription('default')?->trial_ends_at ?? $user->trial_ends_at;
    }

    public function trialDaysLeft(Account $account): ?int
    {
        $endsAt = $this->trialEndsAt($account);

        if ($endsAt === null) {
            return null;
        }

        return max(0, (int) ceil(now()->diffInDays($endsAt, false)));
    }

    /**
     * The "free until it works" cap: leads recovered during the trial.
     */
    public function leadsRemainingInTrial(Account $account): int
    {
        return max(0, (int) config('textback.trial_lead_cap') - $account->leads_recovered_count);
    }

    /**
     * Nudge the operator to subscribe once the value-metered cap is hit.
     */
    public function shouldPromptUpgrade(Account $account): bool
    {
        return ! $this->subscribed($account) && $account->leadCapReached();
    }

    /**
     * Money-back guarantee: within the window and under the minimum-leads bar.
     */
    public function guaranteeEligible(Account $account): bool
    {
        if (! $this->subscribed($account)) {
            return false;
        }

        $subscription = $account->user->subscription('default');
        $start = $subscription?->created_at;

        if ($start === null) {
            return false;
        }

        $withinWindow = $start->gt(now()->subDays((int) config('textback.guarantee_days')));
        $underMinLeads = $account->leads_recovered_count < (int) config('textback.guarantee_min_leads');

        return $withinWindow && $underMinLeads;
    }

    /**
     * Refund the operator's most recent charge under the guarantee.
     * Returns true on success. Best-effort; requires Stripe.
     */
    public function refundGuarantee(Account $account): bool
    {
        if (! $this->guaranteeEligible($account) || ! $this->stripeConfigured()) {
            return false;
        }

        try {
            $user = $account->user;
            $intentId = $user->subscription('default')?->latestPayment()?->id;

            if ($intentId === null) {
                return false;
            }

            $user->refund($intentId);
            $user->subscription('default')?->cancelNow();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Fair-use SMS cap for the account's current plan (null = no plan).
     */
    public function smsCap(Account $account): ?int
    {
        $plan = $this->currentPlanKey($account);

        return $plan ? (int) config("textback.plans.{$plan}.sms_cap") : null;
    }

    public function currentPlanKey(Account $account): ?string
    {
        $priceId = $account->user->subscription('default')?->stripe_price;

        foreach ((array) config('textback.plans') as $key => $plan) {
            if ($plan['price_id'] === $priceId) {
                return $key;
            }
        }

        return $this->subscribed($account) ? 'solo' : null;
    }
}
