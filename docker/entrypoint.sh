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

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
  touch "${DB_DATABASE:-database/database.sqlite}"
fi

wait_for_database

if [ "${APP_ENV:-production}" = "production" ] && [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY is required in production. Refusing to start."
  exit 1
fi

php artisan optimize:clear --no-interaction || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  php artisan migrate --force --no-interaction
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
  php artisan db:seed --force --no-interaction
fi

php artisan storage:link --no-interaction || true

if [ "$#" -gt 0 ]; then
  exec "$@"
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf
