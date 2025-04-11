ARG NODE_VERSION=22

FROM node:${NODE_VERSION} AS node

FROM ghcr.io/boodschrappen/boodschrappen:edge

COPY --from=node /usr/lib /usr/lib
COPY --from=node /usr/local/lib /usr/local/lib
COPY --from=node /usr/local/include /usr/local/include
COPY --from=node /usr/local/bin /usr/local/bin

COPY package*.json .

RUN npm i \
    # Undo optimizations done in the base image.
    && php artisan optimize:clear \
    # We do need to cache the icon set though
    && php artisan icons:cache

CMD ["php", "artisan", "octane:start", "--watch", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]
