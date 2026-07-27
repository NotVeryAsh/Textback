#!/usr/bin/env bash
set -euo pipefail

# Run the developer's Cloudflare Tunnel as part of `composer run dev`.
# No-op when cloudflared isn't installed or no tunnel name is configured,
# so devs who haven't opted in to tunneling still get the rest of the stack.
#
# Twilio and Stripe webhooks need a public URL to reach this app. In local
# dev, run a named Cloudflare tunnel pointing at the Herd hostname, set
# CLOUDFLARED_TUNNEL_NAME in .env, and point your Twilio number's webhooks at
# the tunnel URL. Not required for the local simulator.

ENV_FILE="$(cd "$(dirname "$0")/.." && pwd)/.env"
if [[ -f "$ENV_FILE" ]]; then
    set -a
    # shellcheck disable=SC1090
    source "$ENV_FILE"
    set +a
fi

if ! command -v cloudflared >/dev/null 2>&1; then
    echo "[tunnel] cloudflared not installed - skipping. Install with: brew install cloudflared"
    exit 0
fi

TUNNEL_NAME=${CLOUDFLARED_TUNNEL_NAME:-}
if [[ -z "$TUNNEL_NAME" ]]; then
    echo "[tunnel] CLOUDFLARED_TUNNEL_NAME not set - skipping. See README -> Cloudflare Tunnel."
    exit 0
fi

exec cloudflared tunnel run "$TUNNEL_NAME"
