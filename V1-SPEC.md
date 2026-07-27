# Textback — v1 Build Spec

Companion to [PLAN.md](./PLAN.md). This is the buildable v1: scope, the customer offer, architecture, data model, flows, compliance, and build order.

---

## 1. v1 scope

**In:**
- **Pillar 1 — Missed-call text-back** (the core; ship first).
- **Pillar 2 — Review requests** (manual one-tap + bulk).
- Two-way SMS (lead replies route to the operator).
- Multi-vertical templates (realtor / contractor / freelancer) via config.
- Self-serve onboarding wizard + billing + the offer (below).

**Out (v2+):**
- Pillar 3 (invoice reminders for trades / lead-nurture drips for realtors) — design the data model to allow it, don't build it yet.
- CRM/transaction-software integrations (auto-trigger reviews on "deal closed").
- Number porting (start with a fresh Twilio number; add porting later).
- Team/multi-seat, analytics beyond a basic dashboard, mobile app.

**v1 done = a solo operator can:** sign up → get a number → forward their calls → miss a call → the lead gets an auto-text → the lead's reply reaches them → they can one-tap ask a client for a review → they get billed after the trial.

---

## 2. The Offer (evidence-backed — the thing clients latch onto)

**Goal:** maximum "tasty + safe" for the customer, without a forever-free tier bleeding your per-user Twilio costs.

### What the evidence says
- **Card-required ("opt-out") trials convert at 60.4%** vs no-card 25.2% vs reverse-trial 38.4% vs **freemium just 4.7%**. Forever-free is the *worst* converter — and here it also costs you real money per signup.
- **14 days is the optimal trial length** (62% of SaaS use it); it creates urgency a 30-day trial loses. What matters is hitting the "aha" in day 1–3 — which for us is *a real lead getting texted back*.
- **Competitors are all high-friction:** Podium = demo + annual contract; Thryv = demo + **$250 onboarding fee**; Signpost/Birdeye = demo-gated, custom quotes. **None offer clean self-serve, no-contract, low-risk onboarding.** That's our positioning wedge.

### The Textback offer (recommended)
A **value-metered free trial + money-back guarantee + concierge setup + no contract:**

1. **"Free until it works" trial** — free up to your **first 5 recovered leads** *or* **14 days**, whichever comes first. Value-metered so they *experience the aha* (a real lead texted back) before paying. Card on file to start (opt-out → best conversion + filters tyre-kickers), **not charged until the trial ends**.
2. **30-day money-back guarantee** on the first paid month — **outcome-based**, not revenue-based: *"If Textback doesn't recover at least N leads / send N review requests in your first 30 days, full refund."* Attributable, in your control, no disputes over unprovable commissions.
3. **Free concierge onboarding** — you (or the wizard) set up their number, forwarding, and templates. This kills the #1 blocker (getting calls to flow through Twilio) **and** undercuts Thryv's $250 setup fee.
4. **No contract, cancel anytime** — the anti-Podium.

**Positioning line:** *"No demo, no contract, no risk. Live in 10 minutes — free until it recovers your first 5 leads, money-back if it doesn't work."*

### Why this beats a plain free tier
- Converts ~13× better than freemium (60.4% vs 4.7%).
- Bounds your cost: a trial consumes ≤ 5 leads' worth of SMS/voice + ~14 days of one number (a few dollars), not an open-ended free number forever.
- The guarantee (not free-forever) carries the risk-reversal, at zero cost to you when the product works.

### Exception — warm first customers
For hand-picked design partners (e.g. the brother-in-law + first ~5), skip the card: **free white-glove concierge** in exchange for feedback + a testimonial + referrals. Card-required opt-out is for the *public self-serve* funnel later.

---

## 3. Pricing & tiers

| Tier | Price | Includes |
|---|---|---|
| **Trial** | Free | ≤ 5 recovered leads or 14 days; full features; card on file |
| **Solo** | **$49/mo** | 1 number, all pillars, fair-use SMS/min cap |
| **Team/Pro** | **$99/mo** | Higher volume, multiple agents/numbers, branding |
| Annual | 2 months free | Both tiers |

- Anchor against Podium $399–999/mo.
- Fair-use caps per tier on SMS + call-minutes; overage passed through or soft-throttled (protects margin).

---

## 4. Unit cost & margin (per customer / month, rough US)

| Item | Cost |
|---|---|
| Twilio number | ~$1.15 |
| Call forwarding (both legs) | ~$4–6 |
| SMS (in + out) | ~$2–5 |
| A2P 10DLC | one-time ~$20 + ~$2/mo (platform-level) |
| **Total** | **~$10–15** |

Charge $49 → **~$35–40 gross profit (~75%)**. Costs are usage-based → enforce tier caps.

---

## 5. Architecture & stack

Reuse the stack you already know (matches Grow-a-Plant):

- **Laravel 12 / PHP 8.4** — app + API + webhook endpoints.
- **Livewire** (or Blade) — onboarding wizard + dashboard.
- **Postgres** — data. **Redis + Horizon** — queues (delayed review sends, retries, async SMS).
- **Twilio PHP SDK** — Voice (`<Dial>` bridging) + Programmable Messaging.
- **Laravel Cashier (Stripe)** — trials, card-on-file, subscriptions, refunds.
- **Sentry** — error tracking.

Webhook endpoints are the heart of it:
- `POST /voice` — inbound call → return TwiML that dials the operator.
- `POST /after-dial` — dial result → if unanswered, text the lead.
- `POST /sms-inbound` — lead replies → route to the operator.
- `POST /stripe/webhook` — billing events.

---

## 6. Core flows

### 6.1 Missed-call text-back
```
Lead calls the operator's Twilio number
  → Twilio POSTs /voice
  → app returns <Dial answerOnBridge timeout=18 action=/after-dial callerId=…><Number>operatorCell</Number></Dial>
  → Twilio bridges to operator's real phone (rings normally)
  → operator ANSWERS → they talk → done
  → operator MISSES → Twilio POSTs /after-dial with DialCallStatus != completed
        → app sends SMS to the original caller (the lead)
        → app records a Lead (status: texted_back)
```
`/after-dial` handler (pseudocode):
```php
if ($request->DialCallStatus !== 'completed' && !recentlyTexted($request->From)) {
    $sms = renderTemplate($account->missed_call_template, $account); // "Hi, it's Sarah…"
    Twilio::message($request->From, $account->twilio_number, $sms);
    Lead::create(['account_id'=>$account->id,'phone'=>$request->From,'status'=>'texted_back']);
}
return response(TwiML::empty(), 200)->header('Content-Type','text/xml');
```
Guards: dedupe within N minutes; ignore if caller == operator; respect quiet hours (optional).

### 6.2 Two-way reply routing
```
Lead replies to the text
  → Twilio POSTs /sms-inbound (From=lead, To=twilio_number)
  → app appends to the Lead's Conversation
  → app forwards the message to the operator (SMS to their cell, or push to dashboard inbox)
  → operator replies (from dashboard, or by texting a command) → app sends via Twilio to the lead
```
v1 can start simple: forward the lead's reply to the operator's cell so they just text back through the app. (Full two-way inbox = polish.)

### 6.3 Review request (manual, pillar 2)
```
Operator taps "Request review" on a client (single) OR imports a list (bulk)
  → app queues an SMS with the Google review one-tap link (g.page/r/…)
  → optional delay (send 1–2 days later) via Horizon
  → app records ReviewRequest (sent → clicked? if trackable)
```
No sentiment gating (Google bans it) — operator chooses who to ask.

---

## 7. Data model (v1 tables)

- **accounts** — id, owner_user_id, business_name, vertical (`realtor|contractor|freelancer`), operator_cell, twilio_number, google_review_link, timezone, quiet_hours, plan, trial_ends_at, leads_recovered_count.
- **users** — auth (Laravel default).
- **numbers** — id, account_id, twilio_sid, e164, status. (1:1 for v1; table allows >1 later.)
- **leads** — id, account_id, phone, first_seen_at, status (`texted_back|replied|converted|ignored`), source (`missed_call`).
- **conversations / messages** — id, lead_id, direction (`in|out`), body, twilio_sid, status, created_at.
- **review_requests** — id, account_id, client_name, phone, status (`queued|sent|clicked`), scheduled_at, sent_at.
- **templates** — id, account_id, kind (`missed_call|review`), body (with merge tags `{{business}}`,`{{agent}}`), is_default.
- **subscriptions / billing** — via Cashier tables.
- **usage_counters** — account_id, period, sms_sent, call_minutes, leads_recovered (for caps + the guarantee metric).

Pillar-3-ready: add `sequences` + `sequence_steps` in v2 (invoice reminders / nurture drips) keyed to account.vertical.

---

## 8. Twilio config & webhooks

1. Provision a number (Voice + SMS) via API on signup.
2. Set the number's Voice URL → `POST /voice`; Messaging URL → `POST /sms-inbound`.
3. `/voice` returns the `<Dial>` TwiML (§6.1) with `action=/after-dial`.
4. Validate Twilio request signatures on all webhooks.
5. Handle statuses: `no-answer`, `busy`, `failed` → text-back; `completed` → nothing.

---

## 9. Onboarding wizard (the 10-minute setup)

1. **Sign up** (email/Google) + business name + vertical.
2. **Provision number** (auto) — show it.
3. **Set forwarding / go-live:**
   - Easy path: "Use this as your business number — put it on Google Business Profile, site, signage" (guide + links).
   - Alt path: conditional call-forwarding codes for their carrier (keep own number).
   - (Porting = later.)
4. **Confirm operator cell** (where calls forward + notifications go) — verify via OTP.
5. **Paste Google review link** (with a "how to find it" helper).
6. **Pick/edit templates** (pre-filled per vertical).
7. **Test call** — call the number, don't answer, confirm the text-back lands. ← the aha moment.
8. **Add card** (trial starts; not charged yet).

Concierge mode: you do steps 2–7 for early customers.

---

## 10. A2P 10DLC compliance checklist (do this before sending any SMS)

- Register your **Brand** (business identity) with The Campaign Registry via Twilio.
- Register a **Campaign** (use case: customer care / mixed) with sample messages (missed-call reply, review request).
- Attach numbers to the messaging service / campaign.
- **Consent:** missed-call text-back to an inbound caller = expected/transactional, but still include business identity + opt-out ("Reply STOP") in messages.
- Honor **STOP/HELP** automatically (Twilio Advanced Opt-Out or your own handler).
- Keep quiet-hours defaults (avoid late-night texts → TCPA hygiene).
- Budget ~$20 one-time + small per-message carrier fees; vetting can take days → start early.

---

## 11. Billing logic (Stripe / Cashier)

- Signup collects card (opt-out trial) → `trial_ends_at = now()+14d`.
- Trial ends when **14 days pass OR `leads_recovered_count >= 5`**, whichever first → convert to Solo $49 unless canceled.
- **Guarantee:** within 30 days of first charge, if `leads_recovered < N` (or on request), issue full refund via Cashier.
- Enforce tier caps against `usage_counters`; soft-throttle or bill overage.
- Dunning for failed payments (Cashier handles).

---

## 12. Multi-vertical config

One codebase, vertical set per account drives:
- **Templates** (missed-call + review copy tuned per vertical).
- **Pillar 3 (v2)**: `contractor|freelancer` → invoice/payment reminders; `realtor` → lead-nurture + past-client/referral drips.
- Labels in UI ("clients" vs "leads").

Ship realtor + contractor + freelancer template packs at launch; the engine is identical.

---

## 13. Build order & rough timeline (solo)

1. **Week 1** — Twilio account, A2P registration started, `/voice` + `/after-dial`, missed-call text-back working end-to-end on one hardcoded account.
2. **Week 2** — Auth, accounts, number provisioning, onboarding wizard, templates, dashboard (leads list).
3. **Week 3** — Two-way reply routing, review-request (manual + bulk), quiet hours/dedupe/STOP handling.
4. **Week 4** — Stripe/Cashier: trial, card-on-file, guarantee/refund, usage caps; polish + test-call flow.
5. **Buffer** — A2P vetting wait, real-device testing, first concierge onboarding (brother-in-law).

≈ **4–6 weeks** to a sellable v1. Critical-path risk = A2P vetting time → start it day 1.

---

## 14. Open questions / risks

- **A2P vetting duration** — start immediately; may gate go-live.
- **Caller-ID choice** on the forward leg (show lead's number vs Twilio number vs whisper) — pick during Week 1 testing.
- **Number strategy** — new number now; porting is a strong v2 (removes the "advertise a new number" friction).
- **Guarantee metric N** — set from the first few customers' real recovered-lead counts so it's always achievable.
- **Two-way inbox depth** — v1 = forward-to-cell; full threaded inbox is polish.

---

## Sources (offer research)

- [SaaS free-trial conversion benchmarks — opt-out 60.4% vs opt-in 25.2% vs freemium 4.7%](https://www.shno.co/marketing-statistics/free-trial-conversion-statistics)
- [SaaS free trial conversion statistics 2026 (84,200-trial study)](https://visionary-marketing.co.uk/blog/saas-free-trial-conversion-statistics-2026)
- [GoHighLevel free trial (14-day standard / 30-day) details](https://automatethejourney.com/blog/gohighlevel-free-trial-2026)
- [Thryv pricing — $250 onboarding fee, demo-led](https://www.thryv.com/pricing/)
- [Birdeye vs Podium vs Signpost (demo-gated incumbents)](https://www.signpost.com/blog/podium-vs-birdeye-vs-signpost/)
- [Podium pricing 2026 (demo + contract friction)](https://www.g2.com/products/podium/pricing)
