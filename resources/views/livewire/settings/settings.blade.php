<div class="py-8">
    <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900">Settings</h1>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
        @endif

        <form wire:submit="save" class="space-y-6">
            {{-- Business --}}
            <div class="rounded-xl bg-white p-5 shadow ring-1 ring-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Business</h2>
                <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Business name</label>
                        <input type="text" wire:model="business_name" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        @error('business_name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Type</label>
                        <input type="text" value="{{ $verticalLabel }}" disabled class="mt-1 block w-full rounded-md border-gray-200 bg-gray-50 text-sm text-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Your mobile (forward + alerts)</label>
                        <input type="text" wire:model="operator_cell" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Google review link</label>
                        <input type="text" wire:model="google_review_link" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm" placeholder="https://g.page/r/...">
                    </div>
                </div>
            </div>

            {{-- Call handling --}}
            <div class="rounded-xl bg-white p-5 shadow ring-1 ring-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Call handling</h2>
                <div class="mt-3 space-y-3">
                    <label class="block text-sm font-medium text-gray-700">When a call rings your phone, show</label>
                    @foreach ($callerModes as $mode)
                        <label class="flex items-start gap-3 rounded-md border border-gray-200 p-3">
                            <input type="radio" wire:model="caller_id_mode" value="{{ $mode->value }}" class="mt-1 text-indigo-600">
                            <span>
                                <span class="block text-sm font-medium text-gray-800">{{ $mode->label() }}</span>
                                <span class="block text-xs text-gray-500">{{ $mode->description() }}</span>
                            </span>
                        </label>
                    @endforeach

                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quiet hours start</label>
                            <input type="time" wire:model="quiet_hours_start" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quiet hours end</label>
                            <input type="time" wire:model="quiet_hours_end" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Templates --}}
            <div class="rounded-xl bg-white p-5 shadow ring-1 ring-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Messages</h2>
                <p class="mt-1 text-xs text-gray-500">Tags: <code>@{{business}}</code>, <code>@{{agent}}</code>, <code>@{{review_link}}</code>.</p>
                <div class="mt-3 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Missed-call text-back</label>
                        <textarea wire:model="tpl_missed" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm"></textarea>
                        @error('tpl_missed') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Review request</label>
                        <textarea wire:model="tpl_review" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm"></textarea>
                        @error('tpl_review') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save settings</button>
        </form>
    </div>
</div>
