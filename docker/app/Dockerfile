FROM node:22-alpine AS frontend-builder

WORKDIR /app

COPY package.json yarn.lock* gulpfile.cjs ./
RUN corepack yarn install --frozen-lockfile

COPY resources ./resources
RUN corepack yarn theme:build

FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    bash git curl unzip icu-dev oniguruma-dev libzip-dev postgresql-dev \
    && docker-php-ext-install intl mbstring zip pdo pdo_pgsql opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --from=frontend-builder /app/public/build /var/www/html/public/build

# non-root user (nice for dev)
RUN addgroup -g 1000 app && adduser -G app -g app -s /bin/sh -D -u 1000 app
RUN chown -R app:app /var/www/html
USER app