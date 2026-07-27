<div class="py-8">
    <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Billing</h1>
            <p class="text-sm text-gray-500">Free until Textback recovers your first {{ config('textback.trial_lead_cap') }} leads. No contract. Money-back guarantee.</p>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        @unless ($stripeConfigured)
            <div class="rounded-md bg-amber-50 p-3 text-sm text-amber-800">
                Stripe is not configured. Add <code>STRIPE_KEY</code>, <code>STRIPE_SECRET</code>, and price IDs to <code>.env</code> to enable checkout.
            </div>
        @endunless

        {{-- Status --}}
        <div class="rounded-xl bg-white p-5 shadow ring-1 ring-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Current status</h2>
            <div class="mt-3 text-sm text-gray-700">
                @if ($subscribed)
                    <p>You are subscribed to the <span class="font-semibold">{{ ucfirst($planKey ?? 'Solo') }}</span> plan.</p>
                    @if ($subscription?->onGracePeriod())
                        <p class="mt-1 text-amber-700">Ends {{ $subscription->ends_at?->toFormattedDayDateString() }}.
                            <button wire:click="resume" class="font-semibold underline">Resume</button>
                        </p>
                    @else
                        <button wire:click="cancel" class="mt-2 text-sm font-semibold text-red-600 underline">Cancel subscription</button>
                    @endif
                @elseif ($onTrial)
                    <p>Free trial: <span class="font-semibold">{{ $leadsRemaining }}</span> free leads left @if ($trialDaysLeft !== null), {{ $trialDaysLeft }} days remaining @endif.</p>
                @else
                    <p>Your trial has ended. Subscribe to keep catching missed calls.</p>
                @endif
            </div>
        </div>

        {{-- Plans --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            @foreach ($plans as $key => $plan)
                <div class="rounded-xl bg-white p-5 shadow ring-1 ring-gray-100">
                    <h3 class="text-base font-semibold text-gray-900">{{ $plan['name'] }}</h3>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $plan['price_label'] }}</p>
                    <ul class="mt-3 space-y-1 text-sm text-gray-600">
                        <li>Missed-call text-back</li>
                        <li>Review requests</li>
                        <li>{{ number_format($plan['sms_cap']) }} texts / month</li>
                        @if ($key === 'team')<li>Multiple agents + branding</li>@endif
                    </ul>
                    @if ($subscribed && $planKey === $key)
                        <p class="mt-4 text-sm font-semibold text-green-600">Current plan</p>
                    @else
                        <button wire:click="subscribe('{{ $key }}')" @disabled(! $stripeConfigured) class="mt-4 w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
                            Choose {{ $plan['name'] }}
                        </button>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Guarantee --}}
        @if ($guaranteeEligible)
            <div class="rounded-xl bg-gray-50 p-5 ring-1 ring-gray-200">
                <h2 class="text-sm font-semibold text-gray-900">Money-back guarantee</h2>
                <p class="mt-1 text-sm text-gray-600">You are within the {{ config('textback.guarantee_days') }} day window and Textback has not recovered {{ config('textback.guarantee_min_leads') }} leads yet. If it is not working for you, get a full refund.</p>
                <button wire:click="requestGuarantee" class="mt-3 rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-gray-300 hover:bg-gray-100">Request refund</button>
            </div>
        @endif
    </div>
</div>
