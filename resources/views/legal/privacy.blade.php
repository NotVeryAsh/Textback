@php $company = 'Phillips Co'; $brand = 'Textback'; $domain = 'text-back.net'; @endphp

<x-legal-layout title="Privacy Policy" updated="August 2026">
<p>{{ $brand }} ("{{ $brand }}", "we", "us") is operated by {{ $company }}. This policy explains what information we collect, how we use it, and your choices. It applies to {{ $domain }} and the {{ $brand }} service.</p>

<h2>Information we collect</h2>
<ul>
    <li><strong>Account information</strong> you provide (name, business name, email, mobile phone number).</li>
    <li><strong>Contact information</strong> for the leads and customers you communicate with through the service (phone numbers, message content).</li>
    <li><strong>Usage data</strong> such as calls handled, messages sent, and feature activity.</li>
</ul>

<h2>How we use information</h2>
<ul>
    <li>To provide the service: forwarding calls, sending automated text-back replies, review requests, and follow-up messages on your behalf.</li>
    <li>To verify your phone number during setup via a one-time passcode.</li>
    <li>To operate, secure, support, and improve the service and to process billing.</li>
</ul>

<h2>SMS and mobile messaging</h2>
<p><strong>We do not sell, rent, or share mobile phone numbers or SMS opt-in data with third parties or affiliates for their marketing purposes.</strong> Mobile information is used only to deliver the messaging service described here.</p>
<p><strong>Message frequency varies</strong> based on your activity and call volume (for example, a text is sent when a call is missed, and reminders may be sent on a schedule you configure).</p>
<p><strong>Message and data rates may apply.</strong> Reply <strong>STOP</strong> to any message to unsubscribe, or <strong>HELP</strong> for help. Carriers are not liable for delayed or undelivered messages.</p>

<h2>Sharing with service providers</h2>
<p>We share data only with vendors that help us run the service (for example, our telephony provider Twilio and our payment processor Stripe), under contracts that limit their use of the data to providing services to us. We may disclose information if required by law.</p>

<h2>Data retention and security</h2>
<p>We keep information for as long as your account is active or as needed to provide the service and meet legal obligations, then delete or anonymize it. We use reasonable technical and organizational measures to protect it.</p>

<h2>Your choices</h2>
<p>You can opt out of texts at any time by replying STOP. You can request access to or deletion of your data by contacting us. Message recipients can opt out directly by replying STOP to any message.</p>

<h2>Contact</h2>
<p>Questions about this policy: <a href="mailto:privacy@{{ $domain }}">privacy@{{ $domain }}</a>.</p>
</x-legal-layout>
