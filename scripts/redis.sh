#!/usr/bin/env bash
set -euo pipefail

# Run a local Redis instance for `composer run dev`.
# Skips cleanly when redis-server is missing or already bound to :6379
# so devs running Redis via Herd/launchd still get the rest of the stack.

if ! command -v redis-server >/dev/null 2>&1; then
    echo "[redis] redis-server not installed - skipping. Install with: brew install redis"
    exit 0
fi

if lsof -iTCP:6379 -sTCP:LISTEN -nP >/dev/null 2>&1; then
    echo "[redis] :6379 already in use - assuming Redis is running elsewhere, skipping."
    exit 0
fi

exec redis-server --save "" --appendonly no
