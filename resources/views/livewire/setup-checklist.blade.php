<div>
    @if (! $account->setup_dismissed && ! $allDone)
        <div class="rounded-xl bg-white p-5 shadow ring-1 ring-indigo-100">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Finish setting up ({{ $doneCount }}/{{ $total }})</h2>
                    <p class="text-xs text-gray-500">Optional, but each one gets you more out of Textback.</p>
                </div>
                <button wire:click="dismiss" class="text-xs text-gray-400 hover:text-gray-600">Dismiss</button>
            </div>

            {{-- progress bar --}}
            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-indigo-600 transition-all" style="width: {{ $total ? round($doneCount / $total * 100) : 0 }}%"></div>
            </div>

            <ul class="mt-4 space-y-2">
                @foreach ($tasks as $task)
                    <li class="flex items-center justify-between rounded-md border border-gray-100 px-3 py-2">
                        <span class="flex items-center gap-2 text-sm {{ $task['done'] ? 'text-gray-400 line-through' : 'text-gray-800' }}">
                            @if ($task['done'])
                                <svg class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.1 3.1 6.8-6.8a1 1 0 011.4 0z" clip-rule="evenodd"/></svg>
                            @else
                                <span class="h-4 w-4 rounded-full border-2 border-gray-300"></span>
                            @endif
                            {{ $task['label'] }}
                        </span>

                        @if (! $task['done'])
                            @if ($task['key'] === 'verify')
                                <button wire:click="sendCode" class="text-xs font-semibold text-indigo-600 hover:underline">Verify</button>
                            @elseif ($task['key'] === 'lead')
                                <span class="text-xs text-gray-400">Call your Textback number</span>
                            @elseif (isset($task['route']))
                                <a href="{{ $task['route'] }}" class="text-xs font-semibold text-indigo-600 hover:underline">Set up</a>
                            @endif
                        @endif
                    </li>
                @endforeach
            </ul>

            {{-- inline phone verify --}}
            @if ($verifying)
                <div class="mt-4 rounded-md bg-gray-50 p-3">
                    <p class="text-sm text-gray-700">Enter the 6-digit code we sent to {{ $account->operator_cell }}.</p>
                    @if ($devCode)
                        <p class="mt-1 text-xs text-blue-700">Local dev: your code is <span class="font-mono font-bold">{{ $devCode }}</span>.</p>
                    @endif
                    <div class="mt-2 flex items-center gap-2">
                        <input type="text" wire:model="cell_code" maxlength="6" class="w-32 rounded-md border-gray-300 font-mono text-sm" placeholder="000000">
                        <button wire:click="verify" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Confirm</button>
                    </div>
                    @error('cell_code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            @if ($account->smsPending())
                <p class="mt-4 text-xs text-amber-600">Note: calls forward now, but texting turns on once carrier registration (A2P) clears, usually a few days. We handle it in the background.</p>
            @endif
        </div>
    @endif
</div>
