@php $brand = 'Textback'; $company = 'Phillips Co'; $domain = 'text-back.net'; $supportEmail = 'support@'.$domain; @endphp

<x-legal-layout title="Contact & Support" updated="August 2026">
<p>{{ $brand }} is operated by {{ $company }}. We're a small team and read every message. Here's how to reach us.</p>

<h2>Support</h2>
<p>Email <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a> for help with setup, billing, your number, or anything else. We aim to reply within one business day.</p>

<h2>What to include</h2>
<ul>
    <li>Your account email and business name.</li>
    <li>Your Textback number, if the question is about calls or texts.</li>
    <li>A short description of what happened and what you expected.</li>
</ul>

<h2>Billing</h2>
<p>You can manage your subscription, update your card, and download invoices from the <a href="{{ route('billing') }}">Billing</a> screen in your dashboard, or email us and we'll help.</p>

<h2>Text message help &amp; opt-out</h2>
<p>Recipients of our texts can reply <strong>HELP</strong> for help or <strong>STOP</strong> to opt out at any time. Message and data rates may apply. See our <a href="{{ route('privacy') }}">Privacy Policy</a> and <a href="{{ route('terms') }}">Terms of Service</a>.</p>
</x-legal-layout>
