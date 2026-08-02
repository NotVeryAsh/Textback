@php $domain = 'text-back.net'; $supportEmail = 'support@'.$domain; @endphp

<x-legal-layout title="Contact us" updated="August 2026">
<p>Send us a message and we'll get back to you, usually within a business day.</p>

@if (session('sent'))
    <div style="margin:1rem 0;" class="rounded-md bg-green-50 p-3 text-sm text-green-700">
        Thanks - your message has been sent.
    </div>
@endif

<form method="POST" action="{{ route('contact.send') }}" class="mt-4 space-y-4 not-prose">
    @csrf

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <input id="name" name="name" type="text" required maxlength="120"
               value="{{ old('name', auth()->user()?->name) }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input id="email" name="email" type="email" required maxlength="190"
               value="{{ old('email', auth()->user()?->email) }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
        <textarea id="message" name="message" rows="5" required maxlength="5000"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('message') }}</textarea>
        @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <button type="submit" class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Send</button>
</form>
</x-legal-layout>
