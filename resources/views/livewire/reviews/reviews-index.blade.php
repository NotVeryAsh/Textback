@php use App\Support\Phone; @endphp

<div class="py-8">
    <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Review requests</h1>
            <p class="text-sm text-gray-500">Text happy clients a one-tap link to leave a Google review.</p>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
        @endif

        @unless ($account->google_review_link)
            <div class="rounded-md bg-amber-50 p-3 text-sm text-amber-800">
                Add your Google review link in <a href="{{ route('settings') }}" class="font-semibold underline">Settings</a> so the link shows up in the text.
            </div>
        @endunless

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Single --}}
            <div class="rounded-xl bg-white p-5 shadow ring-1 ring-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Ask one client</h2>
                <div class="mt-3 space-y-3">
                    <input type="text" wire:model="single_name" placeholder="Client name (optional)" class="block w-full rounded-md border-gray-300 text-sm shadow-sm">
                    <input type="text" wire:model="single_phone" placeholder="Phone (+1 415 555 0123)" class="block w-full rounded-md border-gray-300 text-sm shadow-sm">
                    @error('single_phone') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    <button wire:click="sendSingle" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Queue request</button>
                </div>
            </div>

            {{-- Bulk --}}
            <div class="rounded-xl bg-white p-5 shadow ring-1 ring-gray-100">
                <h2 class="text-sm font-semibold text-gray-900">Bulk import</h2>
                <p class="mt-1 text-xs text-gray-500">One per line: <code>Name, +14155550123</code> (name optional).</p>
                <textarea wire:model="bulk" rows="4" class="mt-2 block w-full rounded-md border-gray-300 text-sm shadow-sm" placeholder="Jane Doe, +14155550123&#10;+14155550199"></textarea>
                @error('bulk') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                <button wire:click="sendBulk" class="mt-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Queue all</button>
            </div>
        </div>

        <div class="flex items-center gap-2 text-sm text-gray-600">
            <label>Send after</label>
            <input type="number" min="0" max="30" wire:model="delay_days" class="w-16 rounded-md border-gray-300 text-sm">
            <span>days (0 = now). Best practice is 1 to 2 days after closing.</span>
        </div>

        {{-- History --}}
        <div class="rounded-xl bg-white shadow ring-1 ring-gray-100">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-900">Recent requests</h2>
            </div>
            @if ($requests->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-gray-500">No review requests yet.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($requests as $req)
                        <li class="flex items-center justify-between px-5 py-3 text-sm">
                            <div>
                                <span class="font-medium text-gray-900">{{ $req->client_name ?: Phone::pretty($req->phone) }}</span>
                                <span class="text-gray-400">&middot; {{ Phone::pretty($req->phone) }}</span>
                            </div>
                            <span @class([
                                'rounded-full px-2 py-0.5 text-xs font-medium',
                                'bg-green-100 text-green-700' => $req->status === 'sent',
                                'bg-gray-100 text-gray-700' => $req->status !== 'sent',
                            ])>{{ ucfirst($req->status) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
