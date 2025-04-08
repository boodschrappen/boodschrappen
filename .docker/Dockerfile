ARG PHP_VERSION=8.4
ARG NODE_VERSION=22
ARG COMPOSER_VERSION=latest

FROM node:${NODE_VERSION} AS node

WORKDIR /app

COPY . .

RUN npm ci \
    && npm run build \
    && npm prune --production

FROM composer:${COMPOSER_VERSION} AS vendor

FROM dunglas/frankenphp:1.5-php${PHP_VERSION}

ARG DEBIAN_FRONTEND=noninteractive

RUN install-php-extensions \
    pcntl \
    pdo_pgsql \
    gd \
    intl \
    zip \
    calendar \
    bcmath \
    exif \
    redis

WORKDIR /app

COPY --from=vendor /usr/bin/composer /usr/bin/

COPY --from=node /app .

COPY .docker/php-conf/opcache.ini /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini


# The config:cache command is executed at runtime to load environment variables.
RUN composer install --no-interaction --optimize-autoloader --no-dev \
    && php artisan storage:link \
    && php artisan optimize

ENTRYPOINT ["start-container"]
