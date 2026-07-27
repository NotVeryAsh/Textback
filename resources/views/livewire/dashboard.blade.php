@php use App\Support\Phone; @endphp

<div class="py-8">
    <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $account->business_name }}</h1>
                <p class="text-sm text-gray-500">
                    Textback number:
                    <span class="font-mono">{{ $account->twilio_number ? Phone::pretty($account->twilio_number) : 'not set' }}</span>
                    @if ($account->is_live)
                        <span class="ml-2 inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">Live</span>
                    @endif
                </p>
            </div>
            <a href="{{ route('leads') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">View leads</a>
        </div>

        {{-- Progressive setup checklist (deferred onboarding steps) --}}
        <livewire:setup-checklist />


        {{-- Trial / upgrade banner --}}
        @if ($promptUpgrade)
            <div class="rounded-lg bg-amber-50 p-4 ring-1 ring-amber-200">
                <p class="text-sm font-medium text-amber-900">You have hit your free trial cap of {{ config('textback.trial_lead_cap') }} recovered leads. Textback is working. Add your card to keep it running.</p>
                <a href="{{ route('billing') }}" class="mt-2 inline-block rounded-md bg-amber-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-amber-500">Subscribe</a>
            </div>
        @elseif ($onTrial && ! $subscribed)
            <div class="rounded-lg bg-indigo-50 p-4 ring-1 ring-indigo-100">
                <p class="text-sm text-indigo-900">
                    Free trial: <span class="font-semibold">{{ $leadsRemaining }}</span> of {{ config('textback.trial_lead_cap') }} free leads left
                    @if ($trialDaysLeft !== null) &middot; {{ $trialDaysLeft }} days remaining @endif.
                    <a href="{{ route('billing') }}" class="font-semibold underline">Set up billing</a>
                </p>
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $stats = [
                    ['label' => 'Leads recovered', 'value' => $leadsRecovered, 'hint' => 'Missed calls texted back'],
                    ['label' => 'Replies', 'value' => $repliesCount, 'hint' => 'Leads who texted back'],
                    ['label' => 'Reviews requested', 'value' => $reviewsSent, 'hint' => 'Google review asks sent'],
                    ['label' => 'Texts sent', 'value' => $messagesSent, 'hint' => 'Total outbound messages'],
                ];
            @endphp
            @foreach ($stats as $stat)
                <div class="rounded-xl bg-white p-5 shadow ring-1 ring-gray-100">
                    <dt class="text-sm font-medium text-gray-500">{{ $stat['label'] }}</dt>
                    <dd class="mt-1 text-3xl font-bold text-gray-900">{{ $stat['value'] }}</dd>
                    <p class="mt-1 text-xs text-gray-400">{{ $stat['hint'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Recent leads --}}
        <div class="rounded-xl bg-white shadow ring-1 ring-gray-100">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-900">Recent leads</h2>
                <a href="{{ route('leads') }}" class="text-sm text-indigo-600 hover:underline">See all</a>
            </div>
            @if ($recentLeads->isEmpty())
                <div class="px-5 py-10 text-center text-sm text-gray-500">
                    No leads yet. Miss a call to your Textback number and it will show up here.
                    <div class="mt-2 text-xs text-gray-400">Tip (local): <code>php artisan textback:simulate-missed-call</code></div>
                </div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($recentLeads as $lead)
                        <li class="flex items-center justify-between px-5 py-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $lead->displayName() }}</p>
                                <p class="text-xs text-gray-500">{{ Phone::pretty($lead->phone) }} &middot; {{ $lead->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-{{ $lead->status->color() }}-100 px-2 py-0.5 text-xs font-medium text-{{ $lead->status->color() }}-700">{{ $lead->status->label() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
