@php $brand = 'Textback'; $company = 'Phillips Co'; $domain = 'text-back.net'; $supportEmail = 'support@'.$domain; @endphp

<x-legal-layout title="Contact & Support" updated="August 2026">
<p>{{ $brand }} is operated by {{ $company }}. We're a small team and read every message. Send us a note below, or email <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a> directly. We aim to reply within one business day.</p>

@if (session('sent'))
    <div style="margin:1rem 0;" class="rounded-md bg-green-50 p-3 text-sm text-green-700">
        Thanks - your message has been sent. We'll get back to you soon.
    </div>
@endif

<form method="POST" action="{{ route('contact.send') }}" class="mt-6 space-y-4 not-prose">
    @csrf

    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Your name</label>
        <input id="name" name="name" type="text" required
               value="{{ old('name', auth()->user()?->name) }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Your email</label>
        <input id="email" name="email" type="email" required
               value="{{ old('email', auth()->user()?->email) }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="message" class="block text-sm font-medium text-gray-700">How can we help?</label>
        <textarea id="message" name="message" rows="5" required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('message') }}</textarea>
        @error('message') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <button type="submit" class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Send message</button>
</form>

<h2>Text message help &amp; opt-out</h2>
<p>Recipients of our texts can reply <strong>HELP</strong> for help or <strong>STOP</strong> to opt out at any time. Message and data rates may apply. See our <a href="{{ route('privacy') }}">Privacy Policy</a> and <a href="{{ route('terms') }}">Terms of Service</a>.</p>
</x-legal-layout>
