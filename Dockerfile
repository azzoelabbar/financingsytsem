# syntax=docker/dockerfile:1
#
# ميزان / Mizan — a single self-contained image.
#
# Everything the app needs is built in here: PHP dependencies, the compiled
# front-end assets, and the web server. At startup the container migrates and
# seeds itself, so there is nothing to run by hand.

# ---------------------------------------------------------------------------
# 1. PHP dependencies
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY composer.json composer.lock ./

# Platform requirements are checked against the runtime image below, not this
# builder, so they are ignored here.
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction \
        --ignore-platform-reqs

COPY . .

RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction

# ---------------------------------------------------------------------------
# 2. Front-end assets (Tailwind scans the Blade views, so it needs the app)
#
# Debian, not Alpine: package-lock.json pins the glibc (`-gnu`) builds of
# rollup, lightningcss and the Tailwind oxide binary, which do not run on musl.
# ---------------------------------------------------------------------------
FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .
# resources/css/app.css pulls Tailwind sources out of the framework's views.
COPY --from=vendor /app/vendor ./vendor

RUN npm run build

# ---------------------------------------------------------------------------
# 3. Runtime — FrankenPHP (PHP + Caddy in one process)
# ---------------------------------------------------------------------------
FROM dunglas/frankenphp:php8.4-alpine AS app

# Extensions the accounting engine, localisation and passkeys need.
RUN install-php-extensions \
        pcntl \
        opcache \
        intl \
        bcmath \
        gmp \
        zip \
        gd \
        pdo_sqlite \
        pdo_mysql \
        pdo_pgsql

# Production PHP settings, plus a compiled-code cache.
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf '%s\n' \
        'opcache.enable=1' \
        'opcache.memory_consumption=192' \
        'opcache.max_accelerated_files=20000' \
        'opcache.validate_timestamps=0' \
        'memory_limit=512M' \
        'upload_max_filesize=32M' \
        'post_max_size=32M' \
        'expose_php=Off' \
        > "$PHP_INI_DIR/conf.d/mizan.ini"

WORKDIR /app

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

COPY docker/start-mizan.sh /usr/local/bin/start-mizan
RUN chmod +x /usr/local/bin/start-mizan \
    && mkdir -p /data storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache /data

# The database and uploaded files live here, outside the image, so an
# anonymous volume keeps them even when nobody passes -v.
VOLUME ["/data", "/app/storage"]

# Plain HTTP on :8080 — a reverse proxy or tunnel in front terminates TLS.
ENV SERVER_NAME=":8080"

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=90s --retries=5 \
    CMD wget -q --spider http://127.0.0.1:8080/up || exit 1

LABEL org.opencontainers.image.title="Mizan" \
      org.opencontainers.image.description="ميزان — Arabic-first financial management and ERP" \
      org.opencontainers.image.licenses="MIT"

ENTRYPOINT ["start-mizan"]
