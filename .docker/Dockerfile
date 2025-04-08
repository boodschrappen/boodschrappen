ARG PHP_VERSION=8.4
ARG NODE_VERSION=22
ARG COMPOSER_VERSION=latest

FROM node:${NODE_VERSION} AS node

COPY package*.json .

RUN npm ci \
    && npm run build \
    && npm run prune --production

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

COPY --from=vendor /usr/bin/composer /usr/bin/
COPY --from=node /usr/lib /usr/lib

COPY .docker/php-conf/opcache.ini /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
COPY . .

WORKDIR /app

# The config:cache command is executed at runtime to load environment variables.
RUN composer install --no-interaction --optimize-autoloader --no-dev \
    && php artisan storage:link \
    && php artisan optimize

ENTRYPOINT ["start-container"]
