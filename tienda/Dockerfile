FROM composer:2 AS vendor
WORKDIR /app
RUN composer require firebase/php-jwt --no-interaction --prefer-dist

FROM php:apache
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        libssl-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

COPY ./ /var/www/html
COPY --from=vendor /app/vendor /var/www/html/vendor
EXPOSE 80