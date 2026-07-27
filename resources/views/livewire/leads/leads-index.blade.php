@php use App\Support\Phone; @endphp

<div class="py-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h1 class="mb-4 text-2xl font-bold text-gray-900">Leads</h1>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- List --}}
            <div class="lg:col-span-1">
                <div class="mb-3 flex gap-2 text-xs">
                    <button wire:click="$set('filter', 'all')" @class(['rounded-full px-3 py-1', 'bg-indigo-600 text-white' => $filter === 'all', 'bg-gray-100 text-gray-600' => $filter !== 'all'])>All</button>
                    <button wire:click="$set('filter', 'replied')" @class(['rounded-full px-3 py-1', 'bg-indigo-600 text-white' => $filter === 'replied', 'bg-gray-100 text-gray-600' => $filter !== 'replied'])>Replied</button>
                    <button wire:click="$set('filter', 'texted_back')" @class(['rounded-full px-3 py-1', 'bg-indigo-600 text-white' => $filter === 'texted_back', 'bg-gray-100 text-gray-600' => $filter !== 'texted_back'])>New</button>
                </div>

                <div class="overflow-hidden rounded-xl bg-white shadow ring-1 ring-gray-100">
                    @forelse ($leads as $row)
                        <button wire:click="selectLead({{ $row->id }})" @class([
                            'flex w-full items-center justify-between border-b border-gray-50 px-4 py-3 text-left hover:bg-gray-50',
                            'bg-indigo-50' => $lead && $lead->id === $row->id,
                        ])>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $row->displayName() }}</p>
                                <p class="text-xs text-gray-500">{{ $row->last_contacted_at?->diffForHumans() ?? $row->created_at->diffForHumans() }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-{{ $row->status->color() }}-100 px-2 py-0.5 text-xs font-medium text-{{ $row->status->color() }}-700">{{ $row->status->label() }}</span>
                        </button>
                    @empty
                        <p class="px-4 py-10 text-center text-sm text-gray-500">No leads yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Thread --}}
            <div class="lg:col-span-2">
                @if ($lead)
                    <div class="flex h-full flex-col rounded-xl bg-white shadow ring-1 ring-gray-100">
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $lead->displayName() }}</p>
                                <p class="text-xs text-gray-500">{{ Phone::pretty($lead->phone) }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <select wire:change="setStatus($event.target.value)" class="rounded-md border-gray-300 text-xs">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}" @selected($lead->status === $status)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex-1 space-y-3 overflow-y-auto px-5 py-4" style="max-height: 420px;">
                            @forelse ($lead->messages as $message)
                                <div @class(['flex', 'justify-end' => $message->isOutbound()])>
                                    <div @class([
                                        'max-w-md rounded-2xl px-4 py-2 text-sm',
                                        'bg-indigo-600 text-white' => $message->isOutbound(),
                                        'bg-gray-100 text-gray-800' => ! $message->isOutbound(),
                                    ])>
                                        <p>{{ $message->body }}</p>
                                        <p @class(['mt-1 text-[10px]', 'text-indigo-200' => $message->isOutbound(), 'text-gray-400' => ! $message->isOutbound()])>
                                            {{ $message->created_at->format('M j, g:i a') }}
                                            @if ($message->isOutbound()) &middot; {{ $message->status }} @endif
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-sm text-gray-400">No messages yet.</p>
                            @endforelse
                        </div>

                        <div class="border-t border-gray-100 p-4">
                            <form wire:submit="sendReply" class="flex gap-2">
                                <input type="text" wire:model="reply" placeholder="Type a reply..." class="flex-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Send</button>
                            </form>
                            @error('reply') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @else
                    <div class="flex h-64 items-center justify-center rounded-xl bg-white text-sm text-gray-400 shadow ring-1 ring-gray-100">
                        Select a lead to see the conversation.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
