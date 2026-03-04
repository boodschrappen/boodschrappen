ARG NODE_VERSION=22

FROM node:${NODE_VERSION} AS node

FROM ghcr.io/boodschrappen/boodschrappen:edge

COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/include/node /usr/local/include/node
COPY --from=node /usr/local/bin/node /usr/local/bin/node

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

CMD ["php", "artisan", "octane:start", "--watch", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]
