#!/bin/sh
# ميزان / Mizan — start on macOS or Linux.

set -e
cd "$(dirname "$0")"

echo "=================================================="
echo "   ميزان — Mizan Financial Management & ERP"
echo "=================================================="
echo ""

if ! docker info >/dev/null 2>&1; then
    echo "[!] Docker is not running. Start Docker, then run this again."
    exit 1
fi

echo "Starting Mizan. The first time takes a few minutes."
echo ""

docker compose up -d --build

echo ""
echo "Waiting for the system to become ready…"

i=0
until docker compose exec -T mizan wget -q --spider http://127.0.0.1:8080/up >/dev/null 2>&1; do
    i=$((i + 1))
    if [ "$i" -ge 60 ]; then
        echo "[!] It did not come up in time. See: docker compose logs -f"
        exit 1
    fi
    sleep 5
done

URL="http://localhost:${MIZAN_PORT:-8080}"

echo ""
echo "=================================================="
echo "  Mizan is running.   النظام يعمل الآن."
echo "  $URL"
echo "=================================================="
echo ""

command -v open >/dev/null 2>&1 && open "$URL" || true
command -v xdg-open >/dev/null 2>&1 && xdg-open "$URL" >/dev/null 2>&1 || true
