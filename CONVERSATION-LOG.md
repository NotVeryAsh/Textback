# Textback - Working Session Log

A structured record of the conversation and decisions that produced Textback. This is a faithful summary of the working session (idea exploration through build and go-to-market), not a verbatim transcript. It exists so the reasoning behind the product is not lost.

Product name: **Textback** (directory `~/Herd/textback`, served at https://textback.test).

---

## 1. How we got here (idea funnel)

Started from a broad question: "best industry to build an app in?" Constraints established: **solo, bootstrapped, wants steady income fast, no domain lock-in.** Evidence-backed research ranked categories:

1. B2B micro-SaaS in a boring vertical niche (top)
2. Vertical AI tool (rejected: 80% of AI wrappers projected to die; funded competition)
3. Utilities / Photo / Video (rejected: saturated, low willingness-to-pay, discovery war)
4. Health & fitness (winner-take-all)
5. Prosumer / creator tools

Explored and wrote full plans for several ideas (see `PLAN.md` and sibling exploration under `~/Herd/invoice-chaser`, `~/Herd/utility-app`). Converged on a **B2B SMS tool for local businesses.**

## 2. The product concept

**Textback = a dead-simple, one-vertical SMS tool** that plugs three money leaks, all off one contact list and one Twilio number:

1. **Missed-call text-back** - miss a call, the caller instantly gets a text from your number so they do not call the next business.
2. **Review requests** - one tap (or bulk) texts happy customers a Google review link.
3. **Follow-up sequences** - invoice reminders (trades) or lead/past-client nurture, via a data-driven drip engine.

Demand is pre-validated by **Podium** ($399-999/mo, 4.6 stars, 2,066 reviews, universal complaint = price). The wedge is **price + simplicity + one niche.**

## 3. Key decisions and pivots

- **Offer (evidence-backed):** not forever-free (converts 4.7%, bleeds Twilio cost). Instead: value-metered trial (free until 5 recovered leads or 14 days, card on file, converts ~60%), outcome-based money-back guarantee, free concierge onboarding, no contract. The anti-Podium.
- **Onboarding:** cut from 8 steps to 3 (business -> number -> go live). Research: 72% abandon long flows; 43% abandon forced up-front verification. Deferred steps moved to a dashboard setup checklist. Phone verify moved off the critical path.
- **Google sign-in** added (Socialite) to reduce signup friction.
- **Numbers/A2P reality:** buying a Twilio number is instant and voice works day one; only A2P 10DLC (SMS) takes ~10-15 days. Big players (Podium, GoHighLevel) are CSP/ISVs that register A2P in the background and never block onboarding. Textback does the same: provision instantly, `a2p_status = pending`, voice-first.
- **THE BIG PIVOT (real customer feedback):** a working realtor (brother-in-law, ~2,000-sales/month county) said realtors are a **bad fit** - realtors chase outbound, nobody cold-calls them, so there are no missed inbound calls to catch, and review upside is thin in a competitive market. He pointed at **service trades**. The data agreed. **Target is now home-service trades** (HVAC, plumbing, electrical, etc.), where inbound calls missed on the job are worth $275-$1,200 each and cost shops $45k-$120k/year.

## 4. Go-to-market (see GTM.md)

- **Best verticals:** start with **HVAC or plumbing** (emergency inbound + high ticket + easy to find). Then electrical, garage-door/restoration, roofing.
- **Pitch = money, not features:** "You miss ~1 in 4 calls on the tools. Each is worth hundreds to over a grand. That is tens of thousands a year to whoever answers next. Textback texts them back in 10 seconds. $49/mo, recover one job and it is paid for the year."
- **Channels (evidence):** for cold outreach to trades, **phone-first + in-person door-knock** win (owners answer phones; blue-collar owners are not on LinkedIn). Email is a backup. **SMS is follow-up-only** (cold B2B texting needs consent + gets filtered). LinkedIn: skip for trades.
- **First 10 customers:** no warm intros available, so cold. Pick one trade + one town, build a 30-50 shop list (flag the ones that did not answer), phone-first with the money pitch + free month + concierge setup, run the missed-call demo live, first 10 = free design partners -> testimonials -> referrals.
- **Paid ads ($100 FB):** not worth it - cannot target trade owners on Facebook (built to reach homeowners), and $100 buys ~40 clicks. Spend time on the phone + free presence where trades gather (r/HVAC, Contractor Talk, HVAC-Talk, supply houses) instead.

## 5. Current status

- **Product: built.** Laravel 12 / Livewire 3 / Postgres / Redis+Horizon / Cashier / Twilio SDK. All 3 pillars, slim onboarding, Google sign-in, billing, 62 passing tests. Runs locally with or without Twilio/Stripe/Google creds (degrades gracefully).
- **To demo/sell, remaining work is config + deploy, not features:** a real Twilio number (voice instant), a public URL (Cloudflare tunnel or deploy) so webhooks reach it, A2P registration started (or toll-free for faster SMS), Stripe keys. Strategy = demoable + concierge onboarding for the first 10 (do things that do not scale).

## 6. Repo docs

- `PLAN.md` - business case, competition, realtor analysis (historical)
- `V1-SPEC.md` - functional spec + the offer research
- `V1-IMPLEMENTATION-PLAN.md` - technical build plan
- `PROGRESS.md` - build tracker (all phases done)
- `GTM.md` - go-to-market: who to sell to and how
- `README.md` - setup, config, Cloudflare tunnel, A2P notes
- `CONVERSATION-LOG.md` - this file

## 7. Open next steps (as of last session)

1. Get to "demoable": Twilio number + public URL + a real end-to-end test on your own phone.
2. Optional: build an in-app "demo mode" (fake a missed call + show the text-back) so it can be shown on a laptop with zero setup.
3. Write the outreach kit (cold-call script, voicemail, 3-line email, post-opt-in SMS, one-page flyer).
4. No git repo yet.
