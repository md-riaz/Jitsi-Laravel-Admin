#!/usr/bin/env sh
set -eu

wait_for_database() {
  case "${DB_CONNECTION:-sqlite}" in
    pgsql)
      echo "Waiting for PostgreSQL at ${DB_HOST:-db}:${DB_PORT:-5432}..."
      until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-postgres}" >/dev/null 2>&1; do
        sleep 2
      done
      ;;
    mysql)
      echo "Waiting for MySQL at ${DB_HOST:-db}:${DB_PORT:-3306}..."
      until mysqladmin ping -h "${DB_HOST:-db}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME:-root}" --silent >/dev/null 2>&1; do
        sleep 2
      done
      ;;
  esac
}

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
  touch "${DB_DATABASE:-database/database.sqlite}"
fi

wait_for_database

if [ -f .env ]; then
  php artisan key:generate --force --no-interaction || true
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force --no-interaction
fi

php artisan config:clear --no-interaction || true
php artisan storage:link --no-interaction || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8090}"
