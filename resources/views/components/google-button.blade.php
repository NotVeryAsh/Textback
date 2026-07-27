@props(['label' => 'Continue with Google'])

@if (filled(config('services.google.client_id')))
    <a href="{{ route('google.redirect') }}"
       class="flex w-full items-center justify-center gap-3 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
        <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#EA4335" d="M12 10.2v3.9h5.5c-.24 1.4-1.7 4.1-5.5 4.1-3.3 0-6-2.7-6-6s2.7-6 6-6c1.9 0 3.1.8 3.8 1.5l2.6-2.5C16.7 3.1 14.6 2.2 12 2.2 6.9 2.2 2.8 6.3 2.8 11.4S6.9 20.6 12 20.6c5.3 0 8.8-3.7 8.8-9 0-.6-.06-1-.15-1.4H12z"/>
        </svg>
        {{ $label }}
    </a>

    <div class="my-5 flex items-center gap-3 text-xs text-gray-400">
        <span class="h-px flex-1 bg-gray-200"></span>
        or
        <span class="h-px flex-1 bg-gray-200"></span>
    </div>
@endif
