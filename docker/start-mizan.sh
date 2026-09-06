#!/bin/sh
#
# Everything that has to happen before ميزان can serve a request.
#
# This runs on every container start and is safe to repeat: migrations only
# apply what is missing, and the currency seeder updates in place. A
# non-technical operator never has to run an artisan command by hand.

set -e

DATA_DIR="${MIZAN_DATA_DIR:-/data}"

say() {
    echo "  ▶ $1"
}

echo ""
echo "=================================================="
echo "   ميزان — Mizan Financial Management & ERP"
echo "=================================================="

# ---------------------------------------------------------------------------
# Writable directories. The /data and storage mounts start out empty.
# ---------------------------------------------------------------------------
mkdir -p \
    "$DATA_DIR" \
    storage/app/public \
    storage/app/private \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
chmod -R 777 storage bootstrap/cache "$DATA_DIR" 2>/dev/null || true

# ---------------------------------------------------------------------------
# Application key. Kept in the data volume so that sessions, remembered logins
# and encrypted columns survive a restart or an image rebuild.
# ---------------------------------------------------------------------------
if [ -z "${APP_KEY:-}" ]; then
    if [ ! -s "$DATA_DIR/app.key" ]; then
        php artisan key:generate --show > "$DATA_DIR/app.key"
        say "generated the application key"
    fi
    APP_KEY="$(cat "$DATA_DIR/app.key")"
    export APP_KEY
fi

# ---------------------------------------------------------------------------
# Database. SQLite by default: one file in the data volume, nothing to
# configure. Point DB_CONNECTION at mysql/pgsql to use a server instead.
# ---------------------------------------------------------------------------
DB_CONNECTION="${DB_CONNECTION:-sqlite}"
export DB_CONNECTION

if [ "$DB_CONNECTION" = "sqlite" ]; then
    DB_DATABASE="${DB_DATABASE:-$DATA_DIR/mizan.sqlite}"
    export DB_DATABASE

    if [ ! -f "$DB_DATABASE" ]; then
        touch "$DB_DATABASE"
        say "created a new database"
    fi
    chmod 666 "$DB_DATABASE" 2>/dev/null || true
else
    say "waiting for the $DB_CONNECTION server at ${DB_HOST:-db}…"
    tries=0
    until php -r '
        $dsn = getenv("DB_CONNECTION") . ":host=" . (getenv("DB_HOST") ?: "db")
             . ";port=" . (getenv("DB_PORT") ?: "3306")
             . ";dbname=" . (getenv("DB_DATABASE") ?: "mizan");
        try { new PDO($dsn, getenv("DB_USERNAME"), getenv("DB_PASSWORD")); }
        catch (Throwable $e) { exit(1); }
    ' 2>/dev/null; do
        tries=$((tries + 1))
        if [ "$tries" -ge 60 ]; then
            echo "  ✗ the database never became reachable — giving up."
            exit 1
        fi
        sleep 2
    done
    say "database is reachable"
fi

# ---------------------------------------------------------------------------
# Stale caches from a previous image or a previous set of settings.
# ---------------------------------------------------------------------------
php artisan config:clear --no-interaction >/dev/null 2>&1 || true
php artisan route:clear --no-interaction >/dev/null 2>&1 || true
php artisan view:clear --no-interaction >/dev/null 2>&1 || true

# ---------------------------------------------------------------------------
# Schema and reference data.
# ---------------------------------------------------------------------------
say "applying database updates…"
php artisan migrate --force --no-interaction

say "installing currencies and reference data…"
php artisan db:seed --class=AccountingReferenceSeeder --force --no-interaction

# ---------------------------------------------------------------------------
# Uploaded files served from public/storage.
# ---------------------------------------------------------------------------
if [ ! -e public/storage ]; then
    php artisan storage:link --no-interaction >/dev/null 2>&1 || true
fi

# ---------------------------------------------------------------------------
# Compile config, routes, views and events for speed.
# ---------------------------------------------------------------------------
say "optimising…"
php artisan config:cache --no-interaction >/dev/null
php artisan route:cache --no-interaction >/dev/null
php artisan view:cache --no-interaction >/dev/null
php artisan event:cache --no-interaction >/dev/null 2>&1 || true

echo ""
echo "  ✓ ready — open ${APP_URL:-http://localhost:8080}"
echo "    First visit shows the one-time registration form."
echo ""

# ---------------------------------------------------------------------------
# Serve. Any arguments passed to `docker run` are handed to FrankenPHP.
# ---------------------------------------------------------------------------
if [ "$#" -gt 0 ]; then
    exec frankenphp "$@"
fi

# The image's Caddyfile has moved between FrankenPHP releases.
for caddyfile in /etc/frankenphp/Caddyfile /etc/caddy/Caddyfile; do
    if [ -f "$caddyfile" ]; then
        exec frankenphp run --config "$caddyfile"
    fi
done

# No packaged config: serve public/ directly.
exec frankenphp php-server --root /app/public --listen "${SERVER_NAME:-:80}"
