@php use App\Support\Phone; @endphp

<div class="py-8">
    <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $isInvoice ? 'Invoice reminders' : 'Follow-ups' }}</h1>
            <p class="text-sm text-gray-500">
                {{ $isInvoice
                    ? 'Chase unpaid invoices automatically by text until they are paid.'
                    : 'Keep cold leads and past clients warm with an automatic text sequence.' }}
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
        @endif

        {{-- Form --}}
        <div class="rounded-xl bg-white p-5 shadow ring-1 ring-gray-100">
            @if ($isInvoice)
                <h2 class="text-sm font-semibold text-gray-900">Add an invoice to chase</h2>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <input type="text" wire:model="name" placeholder="Client name (optional)" class="rounded-md border-gray-300 text-sm shadow-sm">
                    <input type="text" wire:model="phone" placeholder="Phone (+1 415 555 0123)" class="rounded-md border-gray-300 text-sm shadow-sm">
                    <input type="text" wire:model="amount" placeholder="Amount (e.g. $500)" class="rounded-md border-gray-300 text-sm shadow-sm">
                    <input type="date" wire:model="due_date" class="rounded-md border-gray-300 text-sm shadow-sm">
                    <input type="text" wire:model="pay_link" placeholder="Payment link (optional)" class="rounded-md border-gray-300 text-sm shadow-sm sm:col-span-2">
                </div>
                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                @error('amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                @error('due_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                <button wire:click="addInvoice" class="mt-3 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Schedule reminders</button>
                <p class="mt-2 text-xs text-gray-400">Reminders go out 1, 7, and 14 days after the due date, and stop as soon as you mark it paid.</p>
            @else
                <h2 class="text-sm font-semibold text-gray-900">Start a follow-up</h2>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <input type="text" wire:model="name" placeholder="Name (optional)" class="rounded-md border-gray-300 text-sm shadow-sm">
                    <input type="text" wire:model="phone" placeholder="Phone (+1 415 555 0123)" class="rounded-md border-gray-300 text-sm shadow-sm">
                </div>
                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                <button wire:click="startNurture" class="mt-3 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Start follow-up</button>
                <p class="mt-2 text-xs text-gray-400">Gentle check-ins go out over the next few weeks. Stop anytime.</p>
            @endif
        </div>

        {{-- List --}}
        <div class="rounded-xl bg-white shadow ring-1 ring-gray-100">
            <div class="border-b border-gray-100 px-5 py-4">
                <h2 class="text-sm font-semibold text-gray-900">Active &amp; recent</h2>
            </div>
            @if ($enrollments->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-gray-500">Nothing yet.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($enrollments as $e)
                        <li class="flex items-center justify-between px-5 py-3 text-sm">
                            <div>
                                <span class="font-medium text-gray-900">{{ $e->displayName() }}</span>
                                <span class="text-gray-400">&middot; {{ Phone::pretty($e->phone) }}</span>
                                @if ($isInvoice && isset($e->context['amount']))
                                    <span class="text-gray-400">&middot; {{ $e->context['amount'] }}</span>
                                @endif
                                <p class="text-xs text-gray-400">
                                    Step {{ $e->current_step }} of {{ $e->sequence->steps->count() }}
                                    @if ($e->next_run_at && $e->isActive()) &middot; next {{ $e->next_run_at->diffForHumans() }} @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs font-medium',
                                    'bg-green-100 text-green-700' => $e->isActive(),
                                    'bg-gray-100 text-gray-700' => ! $e->isActive(),
                                ])>{{ $e->status->label() }}</span>
                                @if ($e->isActive())
                                    <button wire:click="stop({{ $e->id }})" class="text-xs font-semibold text-red-600 hover:underline">
                                        {{ $isInvoice ? 'Mark paid' : 'Stop' }}
                                    </button>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>
