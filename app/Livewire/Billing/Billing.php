<?php

declare(strict_types=1);

namespace App\Livewire\Billing;

use App\Services\Billing\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Billing extends Component
{
    public function subscribe(string $plan, BillingService $billing): ?RedirectResponse
    {
        $priceId = config("textback.plans.{$plan}.price_id");

        if (! $billing->stripeConfigured() || blank($priceId)) {
            session()->flash('error', 'Billing is not configured yet. Add your Stripe keys and price IDs to .env.');

            return null;
        }

        $user = auth()->user();

        $builder = $user->newSubscription('default', $priceId);

        if ($user->trial_ends_at !== null && $user->trial_ends_at->isFuture()) {
            $builder->trialUntil($user->trial_ends_at);
        }

        $checkout = $builder->checkout([
            'success_url' => route('dashboard'),
            'cancel_url' => route('billing'),
        ]);

        return redirect($checkout->url);
    }

    public function cancel(): void
    {
        auth()->user()->subscription('default')?->cancel();
        session()->flash('status', 'Your subscription will end at the close of the billing period.');
    }

    public function resume(): void
    {
        auth()->user()->subscription('default')?->resume();
        session()->flash('status', 'Subscription resumed.');
    }

    /**
     * Stripe's hosted Customer Portal for self-service management (update card,
     * download invoices, cancel). Recommended over building these ourselves.
     */
    public function manageBilling(): ?RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasStripeId()) {
            session()->flash('error', 'No billing account yet - subscribe first.');

            return null;
        }

        return $user->redirectToBillingPortal(route('billing'));
    }

    public function requestGuarantee(BillingService $billing): void
    {
        if ($billing->refundGuarantee(auth()->user()->account)) {
            session()->flash('status', 'Refund issued and subscription cancelled. Sorry it was not a fit.');
        } else {
            session()->flash('error', 'We could not process a guarantee refund automatically. Please contact support.');
        }
    }

    public function render(BillingService $billing): View
    {
        $account = auth()->user()->account;

        return view('livewire.billing.billing', [
            'account' => $account,
            'stripeConfigured' => $billing->stripeConfigured(),
            'subscribed' => $billing->subscribed($account),
            'onTrial' => $billing->onTrial($account),
            'trialDaysLeft' => $billing->trialDaysLeft($account),
            'leadsRemaining' => $billing->leadsRemainingInTrial($account),
            'guaranteeEligible' => $billing->guaranteeEligible($account),
            'planKey' => $billing->currentPlanKey($account),
            'plans' => config('textback.plans'),
            'subscription' => auth()->user()->subscription('default'),
        ]);
    }
}
