ARG PHP_VERSION=8.4
ARG NODE_VERSION=22
ARG COMPOSER_VERSION=latest

FROM node:${NODE_VERSION} AS node

WORKDIR /app

COPY package*.json .

RUN npm ci

COPY . .

RUN npm run build

FROM composer:${COMPOSER_VERSION} AS vendor

FROM dunglas/frankenphp:1.11-php${PHP_VERSION}

ARG DEBIAN_FRONTEND=noninteractive
ARG COMPOSER_FLAGS=--no-dev

ENV WWWGROUP=nobody

RUN install-php-extensions \
    pcntl \
    pdo_pgsql \
    intl \
    zip \
    exif \
    redis

COPY .docker/start-container /usr/local/bin/start-container
COPY .docker/php-conf/opcache.ini /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

RUN apt update -y \
    && apt upgrade -y \
    && apt install --no-install-recommends -y procps \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

COPY --from=vendor /usr/bin/composer /usr/bin/
COPY --from=node /app/public /app/public/

# The config:cache command is executed at runtime to load environment variables.
RUN composer install --no-interaction --optimize-autoloader $COMPOSER_FLAGS \
    && php artisan storage:link \
    && php artisan optimize \
    && php artisan filament:optimize

ENTRYPOINT ["start-container"]
