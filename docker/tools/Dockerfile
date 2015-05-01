FROM php:5.6-cli

RUN apt-get update && \
    apt-get install -y git zlib1g-dev --no-install-recommends && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install zip pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false -o APT::AutoRemove::SuggestsImportant=false zlib1g-dev

WORKDIR /app
