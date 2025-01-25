ARG PHP_VERSION=8.4
ARG NODE_VERSION=20
ARG COMPOSER_VERSION=latest

FROM node:${NODE_VERSION} AS node

FROM composer:${COMPOSER_VERSION} AS vendor

FROM dunglas/frankenphp:1.4-php${PHP_VERSION}

ARG DEBIAN_FRONTEND=noninteractive

RUN install-php-extensions \
    pcntl \
	gd \
	intl \
	zip \
    bcmath \
    ctype \
    intl \
    pdo \
    pdo_pgsql \
    posix \
    session \
    xml \
    exif \
    redis

# COPY docker/php/conf.d/php.ini /usr/local/etc/php/conf.d/99-php.ini

# Install ease of use packages
RUN apt update && apt upgrade -y \
    && apt install -y git bash zsh libjpeg-dev libpng-dev libwebp-dev

# Setup workstation for user
RUN sh -c "$(curl -fsSL https://raw.github.com/robbyrussell/oh-my-zsh/master/tools/install.sh)" \
    && git clone https://github.com/powerline/fonts.git --depth=1 \
    && cd fonts && ./install.sh \
    && cd .. && rm -rf fonts

COPY --from=vendor /usr/bin/composer /usr/bin/

COPY --from=node /usr/lib /usr/lib
COPY --from=node /usr/local/lib /usr/local/lib
COPY --from=node /usr/local/include /usr/local/include
COPY --from=node /usr/local/bin /usr/local/bin

WORKDIR /app

COPY . .

RUN npm i

ENTRYPOINT ["php", "artisan", "octane:start", "--watch", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]
