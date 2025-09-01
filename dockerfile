# ========== Stage 1: PHP deps ==========
FROM composer:2 AS composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-progress
COPY . .
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress \
 && php artisan package:discover --ansi \
 && rm -rf vendor/bin

# ========== Stage 2: Frontend build (PHP + Node + SQLite) ==========
FROM php:8.2-cli-alpine AS nodebuild
# PHP CLI per artisan + Node per Vite + SQLite per bootstrap durante il build
RUN apk add --no-cache nodejs npm git sqlite sqlite-dev \
 && docker-php-ext-install pdo_sqlite
WORKDIR /app
COPY --from=composer /app /app

# Env e DB per permettere ai plugin Vite che lanciano artisan di funzionare
RUN [ -f .env ] || cp .env.example .env \
 && mkdir -p storage && touch storage/database.sqlite \
 && printf "\nDB_CONNECTION=sqlite\nDB_DATABASE=/app/storage/database.sqlite\n" >> .env \
 && php artisan key:generate --force || true

# Install dipendenze JS e build
RUN if [ -f package-lock.json ]; then npm ci; \
    elif [ -f yarn.lock ]; then npm i -g yarn && yarn --frozen-lockfile; \
    elif [ -f pnpm-lock.yaml ]; then npm i -g pnpm && pnpm i --frozen-lockfile; \
    else npm i; fi
RUN npm run build

# ========== Stage 3: Runtime (Nginx + PHP-FPM) ==========
FROM webdevops/php-nginx:8.2-alpine
ENV WEB_DOCUMENT_ROOT=/app/public
WORKDIR /app

# Estensioni PHP runtime (incluso SQLite)
RUN apk add --no-cache \
    php82-tokenizer php82-fileinfo php82-session php82-simplexml \
    php82-pdo_sqlite php82-sqlite3

# Codice + asset buildati
COPY --from=composer /app /app
COPY --from=nodebuild /app/public /app/public
COPY --from=nodebuild /app/resources /app/resources

# Health file statico (Nginx lo serve senza passare da PHP)
RUN printf "ok" > /app/public/healthz

# Permessi cartelle Laravel
RUN chown -R application:application /app/storage /app/bootstrap/cache /app/database \
 && chmod -R ug+rwX /app/storage /app/bootstrap/cache /app/database

# Healthcheck interno (usa il file statico)
HEALTHCHECK --interval=30s --timeout=5s CMD wget -qO- http://127.0.0.1:80/healthz || exit 1

# EntryPoint: key, DB SQLite in storage, cache, migrate, seed
COPY <<'BASH' /usr/local/bin/entrypoint.sh
#!/usr/bin/env sh
set -e
cd /app

# .env e APP_KEY
if [ ! -f .env ]; then
  cp .env.example .env
fi
if ! grep -q "^APP_KEY=base64" .env 2>/dev/null; then
  php artisan key:generate --force
fi

# Permessi cartelle Laravel
chown -R application:application /app/storage /app/bootstrap/cache
chmod -R ug+rwX /app/storage /app/bootstrap/cache

# SQLite dentro storage (scrivibile da php-fpm)
mkdir -p /app/storage
[ -f /app/storage/database.sqlite ] || touch /app/storage/database.sqlite
chown application:application /app/storage/database.sqlite
chmod 664 /app/storage/database.sqlite

# Cache e ottimizzazioni
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true

# Migrazioni e (opz.) seed
php artisan migrate --force || true
if php artisan list --raw | grep -q "db:seed"; then
  php artisan db:seed --force || true
fi

# Avvia supervisord (nginx + php-fpm)
exec supervisord -n
BASH

RUN chmod +x /usr/local/bin/entrypoint.sh
CMD ["/usr/local/bin/entrypoint.sh"]
