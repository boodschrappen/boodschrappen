ARG PHP_VERSION=8.4
ARG NODE_VERSION=22
ARG COMPOSER_VERSION=latest

FROM node:${NODE_VERSION} AS node

WORKDIR /app

COPY . .

RUN npm ci && npm run build

FROM composer:${COMPOSER_VERSION} AS vendor

FROM dunglas/frankenphp:1.5-php${PHP_VERSION}

ARG DEBIAN_FRONTEND=noninteractive

RUN install-php-extensions \
    pcntl \
    pdo_pgsql \
    intl \
    zip \
    exif \
    redis

COPY --from=vendor /usr/bin/composer /usr/bin/
COPY .docker/php-conf/opcache.ini /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
COPY .docker/start-container /usr/local/bin/start-container
COPY --from=node /app /app

WORKDIR /app

# The config:cache command is executed at runtime to load environment variables.
RUN composer install --no-interaction --optimize-autoloader --no-dev \
    && php artisan storage:link \
    && php artisan optimize

ENTRYPOINT ["start-container"]
