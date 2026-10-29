#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
BACKEND_DIR="$ROOT_DIR/backend"
MOBILE_DIR="$ROOT_DIR/mobile"
BACKEND_PORT="${APP_PORT:-$(sed -n 's/^APP_PORT=//p' "$BACKEND_DIR/.env" | tail -n 1)}"
BACKEND_PORT="${BACKEND_PORT:-80}"
CLOUDFLARED_VERSION=2026.10.6
CLOUDFLARED_SHA256=1d356dac769d0dbf62ebe588c4034824bbff98d5790c2f844cc99e0e2f57462d
TUNNEL_PID=''
TUNNEL_LOG=''
BACKEND_STARTED=0

cleanup() {
  local exit_code=$?
  trap - EXIT INT TERM

  if [[ -n "$TUNNEL_PID" ]]; then
    kill "$TUNNEL_PID" 2>/dev/null || true
    wait "$TUNNEL_PID" 2>/dev/null || true
  fi

  if [[ -n "$TUNNEL_LOG" ]]; then
    rm -f "$TUNNEL_LOG"
  fi

  if [[ "$BACKEND_STARTED" == 1 ]]; then
    (cd "$BACKEND_DIR" && ./vendor/bin/sail stop) || true
  fi

  exit "$exit_code"
}

trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM

if command -v cloudflared >/dev/null 2>&1; then
  TUNNEL_BIN="$(command -v cloudflared)"
elif [[ -x /tmp/blubby-cloudflared ]]; then
  TUNNEL_BIN=/tmp/blubby-cloudflared
else
  if [[ "$(uname -s)-$(uname -m)" != Linux-x86_64 ]]; then
    printf 'Install cloudflared for this platform and retry.\n' >&2
    exit 1
  fi

  DOWNLOAD_PATH="$(mktemp /tmp/blubby-cloudflared.XXXXXX)"
  curl --fail --location --silent --show-error \
    "https://github.com/cloudflare/cloudflared/releases/download/${CLOUDFLARED_VERSION}/cloudflared-linux-amd64" \
    --output "$DOWNLOAD_PATH"
  printf '%s  %s\n' "$CLOUDFLARED_SHA256" "$DOWNLOAD_PATH" | sha256sum --check --status
  chmod 700 "$DOWNLOAD_PATH"
  mv "$DOWNLOAD_PATH" /tmp/blubby-cloudflared
  TUNNEL_BIN=/tmp/blubby-cloudflared
fi

BACKEND_STARTED=1
(cd "$BACKEND_DIR" && ./vendor/bin/sail up -d)

backend_ready=0
for _ in {1..45}; do
  if curl --fail --silent --show-error --max-time 2 "http://127.0.0.1:${BACKEND_PORT}/up" >/dev/null; then
    backend_ready=1
    break
  fi
  sleep 1
done

if [[ "$backend_ready" != 1 ]]; then
  printf 'Backend did not become ready on port %s.\n' "$BACKEND_PORT" >&2
  exit 1
fi

TUNNEL_LOG="$(mktemp /tmp/blubby-api-tunnel.XXXXXX.log)"
"$TUNNEL_BIN" tunnel --no-autoupdate --url "http://127.0.0.1:${BACKEND_PORT}" >"$TUNNEL_LOG" 2>&1 &
TUNNEL_PID=$!

API_URL=''
for _ in {1..45}; do
  API_URL="$(sed -nE 's/.*(https:\/\/[[:alnum:]-]+\.trycloudflare\.com).*/\1/p' "$TUNNEL_LOG" | head -n 1)"
  if [[ -n "$API_URL" ]]; then
    break
  fi
  if ! kill -0 "$TUNNEL_PID" 2>/dev/null; then
    cat "$TUNNEL_LOG" >&2
    exit 1
  fi
  sleep 1
done

if [[ -z "$API_URL" ]]; then
  cat "$TUNNEL_LOG" >&2
  printf 'Could not obtain the HTTPS API tunnel URL.\n' >&2
  exit 1
fi

printf 'Backend API: %s\n' "$API_URL"
printf 'Press Ctrl+C to stop Expo, the API tunnel, and Sail.\n'
cd "$MOBILE_DIR"
EXPO_PUBLIC_API_URL="$API_URL" EXPO_UNSTABLE_TUNNEL_V2=1 npx expo start --tunnel -c
