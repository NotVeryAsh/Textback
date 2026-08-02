@php $company = 'Phillips Co'; $brand = 'Textback'; $domain = 'text-back.net'; @endphp

<x-legal-layout title="Terms of Service" updated="August 2026">
<p>These Terms govern your use of {{ $brand }}, a service operated by {{ $company }}. By creating an account or using the service you agree to these Terms.</p>

<h2>The service</h2>
<p>{{ $brand }} helps local businesses respond to missed calls with an automated text-back, request customer reviews, and send follow-up or invoice reminder messages by SMS. Voice calls are forwarded to a phone number you provide.</p>

<h2>Your responsibilities</h2>
<ul>
    <li>You must have the right to contact the phone numbers you message through the service, and you are responsible for the content of messages sent from your account.</li>
    <li>You will comply with all applicable laws and carrier rules, including the TCPA and CTIA guidelines, and will honor opt-out (STOP) requests.</li>
    <li>You will not use the service for prohibited content (for example, unlawful, deceptive, or high-risk categories restricted by carriers).</li>
</ul>

<h2>Messaging terms</h2>
<p>Message frequency varies. Message and data rates may apply. Recipients can reply STOP to unsubscribe or HELP for help. See our <a href="{{ route('privacy') }}">Privacy Policy</a> for how we handle mobile data.</p>

<h2>Subscriptions and billing</h2>
<p>Paid plans are billed on a recurring monthly basis through our payment processor. Free trials convert to a paid subscription unless cancelled. You can cancel at any time; access continues until the end of the current billing period. Refunds are provided as described at sign-up.</p>

<h2>Availability and disclaimers</h2>
<p>The service is provided "as is." We do not guarantee uninterrupted delivery of calls or messages, which depend on third-party carriers. To the fullest extent permitted by law, {{ $company }} is not liable for indirect or consequential damages, and total liability is limited to the fees you paid in the prior three months.</p>

<h2>Termination</h2>
<p>You may stop using the service at any time. We may suspend or terminate accounts that violate these Terms or carrier rules.</p>

<h2>Changes</h2>
<p>We may update these Terms; material changes will be posted here with a new date.</p>

<h2>Contact</h2>
<p><a href="mailto:support@{{ $domain }}">support@{{ $domain }}</a></p>
</x-legal-layout>
