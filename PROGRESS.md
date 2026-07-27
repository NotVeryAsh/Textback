# Textback - Build Progress

Live tracker for the v1 build. See [V1-IMPLEMENTATION-PLAN.md](./V1-IMPLEMENTATION-PLAN.md) for the full plan.

Legend: [ ] todo · [~] in progress · [x] done

## v1 status: COMPLETE (all 3 pillars + slim onboarding + Google sign-in)

App boots at https://textback.test. Full suite: **62 passing**. Runs with or without Twilio/Stripe/Google creds.

## Phase 10 - Onboarding overhaul + Google sign-in (evidence-backed)
- [x] Research: 72% abandon long onboarding; 43% abandon forced up-front verification; keep 3-7 core steps + dashboard checklist
- [x] Cut wizard 8 steps -> **3** (business -> number -> go live); verify/review/templates/test/billing deferred
- [x] Dashboard **SetupChecklist** (progress bar + 4 tasks; inline phone verify off the critical path; dismissible)
- [x] Phone verification moved OUT of signup into the checklist (was the 43%-drop-off step)
- [x] **Google sign-in** (Socialite): routes + GoogleController + themed buttons; gated/404 when unconfigured
- [x] Number/A2P: provision instantly (voice live day 1), `a2p_status=pending` shown in checklist, SMS not blocking (big-player CSP/ISV approach documented)
- [x] Auth UI rebuilt (no Laravel logo; Textback wordmark; indigo theme matching onboarding)
- [x] OTP bypass for local dev via TEXTBACK_REQUIRE_REAL_OTP
- [x] Fixed trial_ends_at cast 500 on /billing + /dashboard

## Phase 1 - Scaffold & environment
- [x] Study sibling repos (Grow-a-Plant, MyHometown) for conventions
- [x] Create pgsql database `sms_revenue_assistant` (user `ash`)
- [x] Scaffold Laravel 12 into project dir
- [x] Install packages (horizon, cashier, livewire, twilio/sdk, laravel-phone, resend, sentry)
- [x] Install Breeze (Blade auth stack)
- [x] Run package installers (horizon, cashier migrations)
- [x] Configure `.env` + `.env.example` (pgsql, redis, queue, all third-party creds documented)
- [x] `config/textback.php` + `config/services.php` twilio block
- [x] Dev scripts (redis/mailpit/tunnel) + `composer dev` / `composer setup`
- [x] Baseline + Cashier migrations run clean on Postgres

## Phase 2 - Domain model
- [x] Enums (Vertical, LeadStatus, LeadSource, MessageDirection, CallerIdMode, TemplateKind)
- [x] Migrations (accounts, phone_numbers, leads, messages, review_requests, templates, usage_counters)
- [x] Models + relationships + casts
- [x] Factories + seeder (demo account: demo@textback.test / password)

## Phase 3 - Twilio + missed-call text-back (the core)
- [x] TwilioClientFactory, TwimlBuilder, SmsSender, NumberProvisioner
- [x] MissedCallHandler (dedupe, operator-skip, lead create, dispatch SMS)
- [x] Webhook controllers: voice, after-dial, whisper, sms, status
- [x] Twilio signature middleware (skips when unconfigured / disabled)
- [x] SendSms / ForwardInboundSms / SendReviewRequest jobs
- [x] artisan `textback:simulate-missed-call` (tested end to end)

## Phase 4 - Leads dashboard
- [x] Dashboard overview (Livewire) - stats, trial banner, recent leads
- [x] Leads list + conversation thread + reply + status change (Livewire)

## Phase 5 - Reviews + settings
- [x] Review requests: single + bulk import + delay (Livewire)
- [x] Settings: templates, quiet hours, caller-id mode, review link, operator cell (Livewire)

## Phase 6 - Billing (Cashier)
- [x] BillingService (trial, subscribed, guarantee, plan caps)
- [x] Trial (14d + 5-lead cap gate) + generic trial on account create
- [x] Subscribe (Stripe Checkout) / cancel / resume / invoices
- [x] Guarantee refund + fair-use cap config

## Phase 7 - Onboarding + multi-vertical
- [x] Onboarding wizard (business -> number -> forwarding -> verify cell -> review link -> templates -> test -> billing)
- [x] Vertical template packs (realtor/contractor/freelancer) seeded on account create
- [x] EnsureAccountOnboarded middleware

## Phase 8 - Tests + polish
- [x] Tests across handlers, services, webhooks, app screens
- [x] `vendor/bin/pint` clean
- [x] Final boot + migrate + seed + smoke verification (pages 200/302, webhooks return correct TwiML)
- [x] README with setup + Twilio/Stripe config + Cloudflare tunnel scoping

## Phase 9 - Pillar 3 (sequences: invoice reminders + nurture)
- [x] Data-driven sequence engine (sequences + sequence_steps + sequence_enrollments)
- [x] Enums (SequenceKind, EnrollmentStatus, MessageChannel-for-future-MMS)
- [x] Per-vertical default sequences in config/textback.php (schedules + wording as DATA)
- [x] SequenceEnroller (invoice reminder from due date, nurture from now) + stop
- [x] SequenceRunner + `textback:run-sequences` command, scheduled every minute + in `composer dev`
- [x] Follow-ups Livewire screen (vertical-aware: Invoices for trades, Follow-ups for realtors)
- [x] Merge-tag context ({{amount}}, {{pay_link}}, {{due_date}}); media_url column for future MMS/PDF
- [x] 9 more tests (engine + screens); 56 total; verified end to end via runner
- [x] Future-proofing: is_editable flag + channel/media columns so paid tiers can customize schedules/wording/MMS by data, no rebuild

## Out of v1 (deliberately deferred; data model leaves room)
- Per-tier custom schedule/wording editors + MMS/PDF sending (columns + flags exist; UI + Twilio media wiring is future)
- CRM / transaction-software integrations (auto-fire review on "deal closed")
- Number porting UI
- AI auto-responder (guarded, premium)
- Team seats; full threaded two-way inbox (v1 = forward-to-cell + in-app reply)
- Google OAuth login (email/password only in v1)

## Key decisions
- Laravel pinned to 12 (packages target 12; create-project defaulted to 13).
- Livewire pinned to 3 (parity with siblings; installer resolved 4).
- Auth: Breeze Blade + Livewire class components (no Volt).
- App fully usable with no Twilio/Stripe creds (features degrade gracefully).
- Postgres local: user `ash`, no password, db `sms_revenue_assistant`.
