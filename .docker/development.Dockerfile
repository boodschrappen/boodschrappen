ARG NODE_VERSION=22

FROM node:${NODE_VERSION} AS node

FROM ghcr.io/jappe999/boodschrappen:edge

COPY --from=node /usr/lib /usr/lib
COPY --from=node /usr/local/lib /usr/local/lib
COPY --from=node /usr/local/include /usr/local/include
COPY --from=node /usr/local/bin /usr/local/bin

# Install ease of use packages
RUN apt update && apt install -y git bash zsh

# Setup workstation for user
RUN sh -c "$(curl -fsSL https://raw.github.com/robbyrussell/oh-my-zsh/master/tools/install.sh)" \
    && git clone https://github.com/powerline/fonts.git --depth=1 \
    && cd fonts && ./install.sh \
    && cd .. && rm -rf fonts

COPY package*.json .

RUN npm i \
    && composer optimize:clear \
    && php artisan icons:cache

ENTRYPOINT ["php", "artisan", "octane:start", "--watch", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]
