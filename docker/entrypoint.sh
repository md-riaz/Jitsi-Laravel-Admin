#!/usr/bin/env sh
set -eu

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

if [ -f .env ]; then
  php artisan key:generate --force --no-interaction || true
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force --no-interaction
fi

php artisan config:clear --no-interaction || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8090}"
