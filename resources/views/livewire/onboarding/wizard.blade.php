@php
    $steps = [
        'business' => 'Your business',
        'number' => 'Your number',
        'activate' => 'Go live',
    ];
    $order = array_keys($steps);
    $currentIndex = array_search($step, $order, true);
@endphp

<div class="py-10">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Set up Textback</h1>
            <p class="mt-1 text-sm text-gray-600">Three quick steps and you are catching missed calls. You can fine-tune the rest from your dashboard.</p>
        </div>

        {{-- Progress --}}
        <ol class="mb-8 flex gap-2 text-xs font-medium">
            @foreach ($steps as $key => $label)
                @php $i = array_search($key, $order, true); @endphp
                <li @class([
                    'rounded-full px-3 py-1',
                    'bg-indigo-600 text-white' => $key === $step,
                    'bg-indigo-100 text-indigo-700' => $currentIndex !== false && $i < $currentIndex,
                    'bg-gray-100 text-gray-500' => $currentIndex === false || $i > $currentIndex,
                ])>{{ $loop->iteration }}. {{ $label }}</li>
            @endforeach
        </ol>

        <div class="rounded-xl bg-white p-6 shadow ring-1 ring-gray-100">
            {{-- STEP 1: business --}}
            @if ($step === 'business')
                <h2 class="text-lg font-semibold text-gray-900">Tell us about your business</h2>
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Business name</label>
                        <input type="text" wire:model="business_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. Sarah Chen Realty">
                        @error('business_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">What do you do?</label>
                        <select wire:model="vertical" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($verticals as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">This picks the right message wording for you (you can edit it later).</p>
                    </div>
                    <button wire:click="saveBusiness" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Continue</button>
                </div>
            @endif

            {{-- STEP 2: number --}}
            @if ($step === 'number')
                <h2 class="text-lg font-semibold text-gray-900">Get your Textback number</h2>
                <p class="mt-1 text-sm text-gray-600">Clients call this number. It rings your phone, and if you miss it, Textback texts them back. It is live for calls the moment you get it.</p>

                @if ($twilioConfigured)
                    <div class="mt-4 space-y-3">
                        <label class="block text-sm font-medium text-gray-700">Preferred area code (optional)</label>
                        <input type="text" wire:model="area_code" maxlength="3" class="block w-32 rounded-md border-gray-300 shadow-sm" placeholder="415">
                        @error('area_code') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        <button wire:click="provisionNumber" wire:loading.attr="disabled" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <span wire:loading.remove wire:target="provisionNumber">Get my number</span>
                            <span wire:loading wire:target="provisionNumber">Finding a number...</span>
                        </button>
                    </div>
                @else
                    <div class="mt-4 rounded-md bg-amber-50 p-3 text-sm text-amber-800">
                        Twilio is not configured, so we cannot buy a number automatically. Enter a number you control (concierge / manual mode).
                    </div>
                    <div class="mt-3 space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Your business number</label>
                        <input type="text" wire:model="manual_number" class="block w-full rounded-md border-gray-300 shadow-sm" placeholder="+1 415 555 0123">
                        @error('manual_number') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        <button wire:click="saveManualNumber" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Use this number</button>
                    </div>
                @endif
            @endif

            {{-- STEP 3: activate --}}
            @if ($step === 'activate')
                <h2 class="text-lg font-semibold text-gray-900">Go live</h2>
                <p class="mt-1 text-sm text-gray-600">Your Textback number is <span class="font-mono font-semibold">{{ $this->account->twilio_number }}</span>.</p>

                <div class="mt-4 rounded-md bg-gray-50 p-4 text-sm text-gray-700">
                    <p class="font-medium text-gray-900">Two ways to route calls through it:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        <li>Use this number as your public number (Google Business Profile, website, signage).</li>
                        <li>Or keep your own number and set carrier "forward on no answer" to it. We can help.</li>
                    </ul>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Your mobile number (calls forward here)</label>
                    <input type="text" wire:model="operator_cell" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="+1 415 555 0123">
                    @error('operator_cell') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button wire:click="finishSetup" class="mt-5 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Finish and go to dashboard</button>
                <p class="mt-3 text-xs text-gray-400">Next: verify your phone, add your Google review link, and start a free trial, all from your dashboard.</p>
            @endif
        </div>
    </div>
</div>
