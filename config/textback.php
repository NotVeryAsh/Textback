<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Trial
    |--------------------------------------------------------------------------
    | The trial ends at whichever comes first: `trial_days` elapsed, OR the
    | account recovering `trial_lead_cap` leads. The lead cap is a UX nudge
    | to subscribe (Stripe still bills at trial_days unless cancelled); it is
    | the "free until it works" value-metered part of the offer.
    */
    'trial_days' => (int) env('TEXTBACK_TRIAL_DAYS', 14),
    'trial_lead_cap' => (int) env('TEXTBACK_TRIAL_LEAD_CAP', 5),

    /*
    |--------------------------------------------------------------------------
    | Money-back guarantee
    |--------------------------------------------------------------------------
    | Within `guarantee_days` of the first charge, if the account recovered
    | fewer than `guarantee_min_leads`, they qualify for a full refund.
    */
    'guarantee_days' => (int) env('TEXTBACK_GUARANTEE_DAYS', 30),
    'guarantee_min_leads' => (int) env('TEXTBACK_GUARANTEE_MIN_LEADS', 5),

    /*
    |--------------------------------------------------------------------------
    | Missed-call handling
    |--------------------------------------------------------------------------
    | Do not re-text the same caller within this window (minutes).
    */
    'dedupe_minutes' => (int) env('TEXTBACK_DEDUPE_MINUTES', 10),

    /*
    |--------------------------------------------------------------------------
    | Phone verification
    |--------------------------------------------------------------------------
    | When false (local dev), the onboarding phone step accepts any correctly
    | formatted 6-digit code instead of matching the real one. Keep TRUE in
    | production so verification actually verifies.
    */
    'require_real_otp' => (bool) env('TEXTBACK_REQUIRE_REAL_OTP', true),

    /*
    |--------------------------------------------------------------------------
    | Plans (fair-use caps + Stripe price IDs)
    |--------------------------------------------------------------------------
    */
    'plans' => [
        'solo' => [
            'name' => 'Solo',
            'price_id' => env('STRIPE_PRICE_SOLO'),
            'price_label' => '$49/mo',
            'sms_cap' => 750,
            'call_minutes_cap' => 500,
        ],
        'team' => [
            'name' => 'Team',
            'price_id' => env('STRIPE_PRICE_TEAM'),
            'price_label' => '$99/mo',
            'sms_cap' => 2500,
            'call_minutes_cap' => 1500,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Vertical template packs
    |--------------------------------------------------------------------------
    | Seeded onto an account when it is created / its vertical changes.
    | Merge tags: {{business}}, {{agent}}, {{review_link}}.
    | (Pillar 3 sending is driven by the `sequences` block below, not these.)
    */
    'verticals' => [
        'realtor' => [
            'label' => 'Realtor',
            'lead_noun' => 'lead',
            'templates' => [
                'missed_call' => "Hi, it's {{agent}} with {{business}}. Sorry I missed your call! Feel free to text me right here and I'll get back to you ASAP. How can I help?",
                'review' => "Hi, it's {{agent}} with {{business}}. It was a pleasure helping you! Would you mind leaving a quick Google review? It really helps: {{review_link}}",
                'nurture' => "Hi, it's {{agent}} with {{business}}, just checking in. Still thinking about making a move? Happy to answer any questions, no pressure.",
            ],
        ],
        'contractor' => [
            'label' => 'Contractor / Home service',
            'lead_noun' => 'customer',
            'templates' => [
                'missed_call' => "Hey, it's {{business}}. Sorry we missed your call! Text us what you need and we'll get right back to you.",
                'review' => 'Thanks for choosing {{business}}! If you were happy with the work, a quick Google review would mean a lot: {{review_link}}',
                'nurture' => 'Hi from {{business}}. Just a reminder your invoice is still open. You can reply here with any questions.',
            ],
        ],
        'freelancer' => [
            'label' => 'Freelancer',
            'lead_noun' => 'client',
            'templates' => [
                'missed_call' => "Hi, it's {{agent}} at {{business}}. Sorry I missed you! Text me here and I'll reply as soon as I can.",
                'review' => 'Thanks for working with {{business}}! A short Google review would help me a lot: {{review_link}}',
                'nurture' => "Hi, it's {{agent}}. Friendly reminder that invoice is still outstanding. Let me know if you need anything to get it settled.",
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pillar 3: sequences (data-driven drip engine)
    |--------------------------------------------------------------------------
    | One default sequence per vertical, seeded on account create. Steps are
    | data (delay + body + channel), NOT code, so future paid tiers can edit
    | the schedule, reword steps, or attach media (MMS/PDF) by changing rows.
    |
    | delay_minutes is measured from the enrollment "base" time: the invoice
    | due date for invoice_reminder, or enrollment time for nurture.
    | Invoice steps may use {{amount}}, {{pay_link}}, {{due_date}} on top of
    | the standard {{business}} / {{agent}} tags.
    */
    'sequences' => [
        'realtor' => [
            'kind' => 'nurture',
            'name' => 'Lead follow-up',
            'trigger' => 'manual',
            'steps' => [
                ['delay_minutes' => 3 * 24 * 60, 'body' => "Hi, it's {{agent}} with {{business}}, just checking in. Still thinking about a move? Happy to help, no pressure."],
                ['delay_minutes' => 10 * 24 * 60, 'body' => "Hi, it's {{agent}} again. Whenever you're ready to talk homes I'm here. Anything I can answer for you?"],
                ['delay_minutes' => 30 * 24 * 60, 'body' => 'Hi from {{agent}} at {{business}}. Just keeping in touch. If you or anyone you know is thinking of buying or selling, I would love to help.'],
            ],
        ],
        'contractor' => [
            'kind' => 'invoice_reminder',
            'name' => 'Invoice reminders',
            'trigger' => 'invoice_due',
            'steps' => [
                ['delay_minutes' => 1 * 24 * 60, 'body' => "Hi, it's {{business}}. Friendly reminder that your invoice for {{amount}} is now due. You can pay here: {{pay_link}}"],
                ['delay_minutes' => 7 * 24 * 60, 'body' => 'Hi from {{business}}, following up on the {{amount}} invoice (due {{due_date}}). Pay here anytime: {{pay_link}}'],
                ['delay_minutes' => 14 * 24 * 60, 'body' => 'Final reminder from {{business}}: the {{amount}} invoice is overdue. Please settle here: {{pay_link}}. Reply with any questions.'],
            ],
        ],
        'freelancer' => [
            'kind' => 'invoice_reminder',
            'name' => 'Invoice reminders',
            'trigger' => 'invoice_due',
            'steps' => [
                ['delay_minutes' => 1 * 24 * 60, 'body' => "Hi, it's {{agent}} at {{business}}. Quick reminder that your invoice for {{amount}} is now due: {{pay_link}}"],
                ['delay_minutes' => 7 * 24 * 60, 'body' => 'Hi, just following up on the {{amount}} invoice (due {{due_date}}). You can pay here: {{pay_link}}'],
                ['delay_minutes' => 14 * 24 * 60, 'body' => 'Final reminder: the {{amount}} invoice is overdue. Please settle here: {{pay_link}}. Thanks so much.'],
            ],
        ],
    ],
];
