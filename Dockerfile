# syntax=docker/dockerfile:1

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json ./
RUN npm install
COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM php:8.3-cli-alpine AS builder
WORKDIR /app

RUN apk add --no-cache \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    sqlite-dev \
    postgresql-dev \
    mysql-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install \
    bcmath \
    intl \
    mbstring \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    zip \
    gd

FROM php:8.3-fpm-alpine AS runtime
WORKDIR /var/www/html

# Install runtime dependencies for extensions
RUN apk add --no-cache \
    bash \
    curl \
    ca-certificates \
    icu-libs \
    libzip \
    oniguruma \
    mysql-client \
    postgresql-client \
    freetype \
    libjpeg-turbo \
    libpng \
    nginx \
    supervisor

# Copy pre-compiled PHP extensions from builder
COPY --from=builder /usr/local/lib/php/extensions /usr/local/lib/php/extensions
COPY --from=builder /usr/local/etc/php/conf.d /usr/local/etc/php/conf.d

# Setup Nginx and Supervisor
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
RUN mkdir -p /var/log/supervisor /var/run/nginx && \
    chown -R www-data:www-data /var/lib/nginx /var/log/nginx

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN chmod +x docker/entrypoint.sh && \
    mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

ENV APP_ENV=local
ENV APP_DEBUG=true
ENV PORT=8090

EXPOSE 8090

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
