# Textback - v1 Technical Implementation Plan

Concrete build plan for the v1 app. Derived from [PLAN.md](./PLAN.md) and [V1-SPEC.md](./V1-SPEC.md). Progress is tracked in [PROGRESS.md](./PROGRESS.md).

Copy discipline: no em/en dashes anywhere in code, comments, or user-facing copy (house rule from the sibling repos). Use hyphens, commas, or parentheses.

---

## 1. Stack (match the sibling repos)

- PHP 8.4, Laravel 12
- Livewire 3 (app UI), Laravel Breeze (Blade stack) for auth scaffolding
- Tailwind (via Vite), Alpine (bundled by Breeze)
- PostgreSQL (`pgsql`, local user `ash`, db `sms_revenue_assistant`)
- Redis + Laravel Horizon (queues: SMS send, delayed review requests, inbound forwarding)
- Laravel Cashier (Stripe) for trials + subscriptions + the guarantee refund
- Twilio PHP SDK (`twilio/sdk`) for Voice bridging + Programmable Messaging
- Resend (mail, prod) / Mailpit (local), Sentry (errors) - wired but optional via env
- Dev runner: `composer dev` via `concurrently` (horizon, pail, vite, mailpit, redis, tunnel)

No Docker. Cloudflare tunnel scoped but off by default (env flag), same pattern as siblings.

---

## 2. Domain model (migrations + Eloquent)

- **users** (Breeze default + `current_account_id` nullable FK). One user can own one account in v1.
- **accounts**
  - `id, user_id, business_name, vertical (enum: realtor|contractor|freelancer), operator_cell (E.164), operator_cell_verified_at, twilio_number (E.164, nullable), twilio_number_sid, google_review_link, timezone (default America/New_York), quiet_hours_start, quiet_hours_end, caller_id_mode (enum: lead|textback|whisper, default lead), leads_recovered_count (int default 0), onboarding_step, is_live (bool), created_at`
- **phone_numbers** (1 per account v1, table allows more): `id, account_id, e164, twilio_sid, capabilities json, status (active|released)`
- **leads**: `id, account_id, phone (E.164), name (nullable), status (enum: texted_back|replied|converted|closed|ignored), source (enum: missed_call|manual), last_contacted_at, created_at`
- **messages**: `id, account_id, lead_id (nullable), direction (in|out), from, to, body, twilio_sid, status, error, sent_at, created_at` (also stores review-request sends)
- **review_requests**: `id, account_id, client_name, phone (E.164), status (queued|sent|failed), scheduled_at, sent_at, message_id (nullable)`
- **templates**: `id, account_id, kind (enum: missed_call|review|nurture), body, is_active` (seeded per vertical on account create)
- **usage_counters**: `id, account_id, period (YYYY-MM), sms_out, sms_in, call_minutes, leads_recovered` (unique account+period)
- Cashier tables (subscriptions, subscription_items) via `cashier:install`. `customer` columns added to `users`.

Enums as PHP backed enums under `app/Enums`.

---

## 3. Services / core logic

- **App\Services\Twilio\TwilioClientFactory** - builds `Twilio\Rest\Client` from config.
- **App\Services\Twilio\NumberProvisioner** - buys/configures a number (voice + sms webhooks). Guarded: no-op with a clear exception if creds absent, so onboarding degrades gracefully and can be run in "concierge/manual" mode.
- **App\Services\Twilio\TwimlBuilder** - builds the `<Dial>` response (timeout, callerId per `caller_id_mode`, `action` url) and empty responses.
- **App\Services\Messaging\SmsSender** - sends SMS via Twilio, records a `messages` row, bumps `usage_counters`. Appends STOP footer when required.
- **App\Services\Leads\MissedCallHandler** - dedupe window, quiet-hours check, create/attach Lead, render missed-call template, dispatch `SendSms` job, increment `leads_recovered_count` + usage.
- **App\Services\Reviews\ReviewRequestService** - queue single/bulk review requests (optional delay), render review template.
- **App\Services\Templates\TemplateRenderer** - merge tags `{{business}} {{agent}} {{review_link}}`.
- **App\Support\PhoneNumber** - normalize to E.164 (libphonenumber via `propaganistas/laravel-phone` or `brick/phonenumber`).

Webhook responsiveness rule: HTTP handlers do the minimum (validate, record, decide) and dispatch SMS sends to the queue so Twilio gets a fast TwiML/200 response.

---

## 4. HTTP surface

### Webhooks (no auth, Twilio signature middleware; Stripe signature via Cashier)
- `POST /webhooks/twilio/voice` -> TwiML: dial operator cell, `action=/webhooks/twilio/after-dial`.
- `POST /webhooks/twilio/after-dial` -> if `DialCallStatus != completed` -> MissedCallHandler -> empty TwiML.
- `POST /webhooks/twilio/sms` -> inbound SMS: attach to lead, mark `replied`, forward to operator (SMS or dashboard), handle STOP/HELP.
- `POST /webhooks/twilio/status` -> message delivery status callbacks (optional, updates `messages.status`).
- `POST /stripe/webhook` -> Cashier default.

### App (auth: Breeze + verified)
- `/dashboard` - Livewire: overview (leads recovered, reviews sent, money-recovered proxy, live status).
- `/leads` - Livewire: lead list + thread + send reply.
- `/reviews` - Livewire: request review (single + bulk import), history.
- `/settings` - Livewire: templates, quiet hours, caller-id mode, google review link, operator cell.
- `/onboarding` - Livewire wizard (steps in section 6 of V1-SPEC).
- `/billing` - Cashier: start/cancel subscription, update card, invoices.

Middleware: `EnsureAccountOnboarded` gates app pages until onboarding done (except onboarding + billing + logout).

---

## 5. Jobs (queued, Redis/Horizon)

- `SendSms` - actually calls Twilio to send (retry/backoff, records status).
- `SendReviewRequest` - renders + sends a queued/delayed review request.
- `ForwardInboundSms` - notifies operator of a lead reply.
- `SyncUsageCounter` - (optional) periodic rollup; v1 increments inline.

---

## 6. Billing + the offer

- Signup collects card via Cashier (opt-out trial). `newSubscription('default', PRICE)->trialDays(14)->create($pm)`.
- Custom trial gate: trial ends at min(trial_ends_at, when `leads_recovered_count >= config('textback.trial_lead_cap')`). A `TrialGate` checks both; when value-cap hit, prompt to subscribe (Cashier still charges at 14d unless canceled - the lead cap is a UX nudge, not a Stripe mechanism, so document clearly).
- Guarantee: `GuaranteeService::eligible($account)` = within 30 days of first charge AND `leads_recovered < config('textback.guarantee_min_leads')`; issues `refund()` via Cashier.
- Fair-use caps per tier from `config('textback.plans')`; enforced in `SmsSender` (soft warn) - hard stop optional.
- Prices: env `STRIPE_PRICE_SOLO`, `STRIPE_PRICE_TEAM` (monthly). Filled by user in Stripe dashboard.

---

## 7. Config + env

- `config/services.php`: `twilio` (sid, token, from-pool / messaging service sid), plus resend/sentry already present.
- `config/textback.php`: trial_lead_cap (5), trial_days (14), guarantee_min_leads, guarantee_days (30), plans (caps + stripe price ids), dedupe_minutes (10), default templates per vertical, quiet-hours defaults.
- `.env.example`: full, documented, with every value the user must fill. All third-party creds blank + commented. App runs locally without Twilio/Stripe creds (features degrade to "not configured" states) so the user can boot it immediately.

---

## 8. Dev tooling

- `composer dev` script (concurrently): horizon, pail, `npm run dev` (vite), mailpit.sh, redis.sh, tunnel.sh.
- `scripts/redis.sh`, `scripts/mailpit.sh`, `scripts/tunnel.sh` copied/adapted from siblings.
- `composer setup` script: install, env copy, key gen, migrate, npm install/build.
- Herd serves the directory at `https://textback.test` automatically (Herd convention). APP_URL set to that.

---

## 9. Build phases (tracked in PROGRESS.md)

1. Scaffold + packages + env + DB + Horizon + dev scripts; app boots at Herd URL.
2. Auth (Breeze) + accounts + onboarding wizard shell + enums + migrations + models + factories.
3. Twilio services + voice/after-dial/sms webhooks + missed-call text-back end to end (test via artisan command that simulates a webhook).
4. Leads dashboard + thread + reply routing (Livewire).
5. Review requests (single + bulk) + templates settings (Livewire).
6. Billing (Cashier): trial, card, subscribe, guarantee, caps.
7. Multi-vertical template packs + polish + seeders + demo data.
8. Tests (Pest/PHPUnit) for handlers/services + a webhook feature test; pint format; final boot verification.

---

## 10. Explicitly out of v1 (stubs/notes only)

Pillar 3 (invoice reminders / nurture drips), CRM integrations, number porting UI, AI auto-responder, team seats, full threaded two-way inbox (v1 = forward-to-cell + basic in-app reply). Data model leaves room; no code.
