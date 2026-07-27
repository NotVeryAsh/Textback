# Textback - SMS Revenue Assistant

Textback is a dead-simple, one-vertical SMS tool for realtors, contractors, and freelancers. It plugs three money leaks:

1. **Missed-call text-back** - when a call to your Textback number is not answered, the caller instantly gets a text from your number so they do not call the next person.
2. **Review requests** - one tap (or bulk) texts happy clients your Google review link.
3. **Follow-up sequences** - the third pillar, per vertical: invoice/payment reminders for trades, lead and past-client nurture for realtors. A data-driven drip engine (see below).

This is the v1. See `PLAN.md`, `V1-SPEC.md`, and `V1-IMPLEMENTATION-PLAN.md` for the product and build docs, and `PROGRESS.md` for build status.

## Stack

- PHP 8.4, Laravel 12, Livewire 3, Laravel Breeze (Blade auth)
- PostgreSQL, Redis + Laravel Horizon (queues)
- Laravel Cashier (Stripe) for trials, subscriptions, and the money-back guarantee
- Twilio PHP SDK for voice bridging + programmable SMS
- Tailwind CSS via Vite; Resend (mail) and Sentry (errors) wired but optional

## Requirements (already available via Herd on this machine)

- PHP 8.4, Composer, Node 20+/npm
- PostgreSQL (database `sms_revenue_assistant`, local user `ash`)
- Redis
- Optional: `mailpit`, `cloudflared`

## Setup

```bash
composer setup     # install, copy .env, key gen, migrate, seed, npm install + build
composer dev       # run redis, horizon, mailpit, logs (pail), vite, tunnel together
```

The app is served by Herd at **https://textback.test**.

Demo login (from the seeder): **demo@textback.test** / **password**.

The app boots and is fully usable with NO Twilio or Stripe credentials. In that mode:
- Onboarding runs in "concierge" mode (enter a number by hand instead of auto-provisioning).
- Outbound texts are recorded with status `skipped_no_twilio` and logged instead of sent.
- Billing checkout is disabled with a clear notice.

Fill the credentials in `.env` to switch everything on.

## Testing without real phone calls

```bash
php artisan textback:simulate-missed-call                 # first account, random caller
php artisan textback:simulate-missed-call 1 --from=+14155551234
php artisan queue:work --once                          # process the queued text-back
php artisan textback:run-sequences                        # fire any due pillar-3 steps now
php artisan test                                       # full suite
```

## Pillar 3: sequences (data-driven drip engine)

Pillar 3 is a generic sequence engine, not hardcoded steps, so future paid tiers can change schedules, reword steps, or attach media by editing data (not code).

- `sequences` + `sequence_steps` + `sequence_enrollments` tables.
- Each vertical seeds a default sequence on account create (see `config/textback.php` -> `sequences`): realtors get a **nurture** sequence, trades get **invoice reminders** (steps at +1/+7/+14 days from the due date).
- The **Follow-ups** screen adapts to the vertical: trades add an invoice (amount, due date, pay link) to chase; realtors start a nurture follow-up.
- `textback:run-sequences` sends every due step and advances the enrollment. It is scheduled every minute (`routes/console.php`) and included in `composer dev` via `schedule:work`. In production, run `php artisan schedule:work` or a cron entry for `schedule:run`.
- Future expansion is built into the schema: `sequence_steps.channel` + `sequence_steps.media_url` (+ `messages.media_url`) support MMS/PDF, and `sequences.is_editable` marks which sequences a paid tier may customize. v1 sends SMS only.

## How the Twilio flow works

```
Lead calls the Textback number
  -> POST /webhooks/twilio/voice   -> TwiML <Dial> forwards to the operator's real cell
  -> operator answers              -> normal call, done
  -> operator misses               -> POST /webhooks/twilio/after-dial (DialCallStatus != completed)
                                      -> text the caller back, capture the Lead
Lead replies by text
  -> POST /webhooks/twilio/sms     -> record message, mark Lead replied, notify operator
```

The operator's carrier/phone is untouched (no app to install). The only requirement is that the Textback number is the number clients dial (use it as the public number, or forward-on-no-answer, or port the number in later).

## Onboarding (kept deliberately short)

Signup research (72% abandon long flows; 43% abandon forced up-front verification) drove a **3-step wizard**: business -> get number -> go live. Everything optional (verify phone, Google review link, first test, billing) lives in a **dashboard setup checklist** with a progress bar, so users reach value fast and finish the rest later. Phone verification is inline in the checklist, not on the signup path.

## Google sign-in

Set `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` (Socialite). The "Continue with Google" buttons appear on login/register only when configured; the routes 404 otherwise. Note: Google does not accept `.test` redirect URIs, so for local dev point `GOOGLE_REDIRECT_URI` at the Cloudflare tunnel URL (or `http://localhost`) and authorize it in the Google console.

## Numbers and A2P 10DLC (why we do not block onboarding)

Buying a Twilio number is **instant**, and voice forwarding works immediately, so the missed-call text-back is live on day one for calls. The 10-15 day wait people hit is only **A2P 10DLC campaign registration for SMS throughput** (brand reg is minutes; a $3 Fast Track cuts campaigns to ~3 days). Big platforms (Podium, GoHighLevel) are CSP/ISVs that register brand + campaign on the customer's behalf via API in the background and never block onboarding; toll-free is a faster-SMS alternative. Textback follows the same model: provision instantly, set `a2p_status = pending`, show "texting turns on once carrier registration clears" in the checklist, and (future) drive the A2P registration API. Local dev without Twilio just records texts as `skipped_no_twilio`.

## Configuring Twilio (production)

1. Set `TWILIO_ACCOUNT_SID` and `TWILIO_AUTH_TOKEN` in `.env`.
2. (Recommended) create a Messaging Service for A2P 10DLC and set `TWILIO_MESSAGING_SERVICE_SID`.
3. Onboarding will provision a number and point its voice + SMS webhooks at this app. Those webhooks must be publicly reachable (see Cloudflare Tunnel below).
4. Register your A2P 10DLC brand + campaign before sending at scale.

## Configuring Stripe (billing)

1. Set `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`.
2. Create two recurring prices (Solo, Team) and set `STRIPE_PRICE_SOLO` / `STRIPE_PRICE_TEAM`.
3. Point a Stripe webhook at `/stripe/webhook` (Cashier handles it).

The offer: free trial until the account recovers `TEXTBACK_TRIAL_LEAD_CAP` leads (or `TEXTBACK_TRIAL_DAYS` days), card on file, with an outcome-based money-back guarantee (`TEXTBACK_GUARANTEE_*`).

## Cloudflare Tunnel (future; scoped, not required yet)

Twilio and Stripe webhooks need a public HTTPS URL. Locally, Herd serves `.test` only on your machine, so to receive real webhooks during development, expose the app with a named Cloudflare tunnel:

```bash
# one-time
brew install cloudflared
cloudflared tunnel login
cloudflared tunnel create textback-dev
# map a hostname you control to the Herd origin, e.g.:
cloudflared tunnel route dns textback-dev textback-dev.yourdomain.com
```

Create `~/.cloudflared/config.yml` pointing the tunnel at the Herd origin:

```yaml
tunnel: textback-dev
credentials-file: /Users/ash/.cloudflared/<tunnel-id>.json
ingress:
  - hostname: textback-dev.yourdomain.com
    service: https://textback.test
    originRequest:
      originServerName: textback.test
  - service: http_status:404
```

Then set in `.env`:

```
CLOUDFLARED_TUNNEL_NAME=textback-dev
APP_URL=https://textback-dev.yourdomain.com
```

`composer dev` runs `scripts/tunnel.sh`, which starts the tunnel when `CLOUDFLARED_TUNNEL_NAME` is set (and no-ops otherwise). Point your Twilio number's webhooks and Stripe webhook at the tunnel hostname. In production, this is replaced by your real domain.

## Project layout

- `app/Enums` - Vertical, LeadStatus, LeadSource, MessageDirection, CallerIdMode, TemplateKind
- `app/Models` - Account, Lead, Message, ReviewRequest, Template, PhoneNumber, UsageCounter, User
- `app/Services` - Twilio (client, TwiML, number provisioner), Messaging (SmsSender), Leads (MissedCallHandler), Reviews, Templates, Accounts, Billing
- `app/Jobs` - SendSms, SendReviewRequest, ForwardInboundSms
- `app/Http/Controllers/Webhooks` - Twilio voice + SMS
- `app/Livewire` - Dashboard, Leads, Reviews, Settings, Onboarding\Wizard, Billing
- `config/textback.php` - trial, guarantee, plans, dedupe, per-vertical template packs
