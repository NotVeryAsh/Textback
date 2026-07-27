#!/usr/bin/env bash
set -euo pipefail

# Run Mailpit (SMTP capture + web UI) for `composer run dev`.
# Skips cleanly when mailpit is missing or its SMTP port is already bound,
# so Herd's bundled Mailpit (or any externally-managed instance) is left alone.

ENV_FILE="$(cd "$(dirname "$0")/.." && pwd)/.env"
if [[ -f "$ENV_FILE" ]]; then
    set -a
    # shellcheck disable=SC1090
    source "$ENV_FILE"
    set +a
fi

if ! command -v mailpit >/dev/null 2>&1; then
    echo "[mailpit] mailpit not installed - skipping. Install with: brew install mailpit"
    exit 0
fi

SMTP_PORT="${MAIL_PORT:-1025}"

if lsof -iTCP:"$SMTP_PORT" -sTCP:LISTEN -nP >/dev/null 2>&1; then
    echo "[mailpit] :$SMTP_PORT already in use - assuming Mailpit is running elsewhere, skipping."
    exit 0
fi

exec mailpit --smtp "0.0.0.0:$SMTP_PORT"
