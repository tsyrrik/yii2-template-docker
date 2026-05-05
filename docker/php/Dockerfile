# syntax=docker/dockerfile:1.7

FROM php:8.3-fpm AS php-base

WORKDIR /var/www/app

# kics-scan ignore-line
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    bash \
    git \
    tzdata \
    curl \
    libfcgi-bin \
    zip \
    unzip \
 && rm -rf /var/lib/apt/lists/* \
 && mkdir -p /var/www/app \
 && chown -R www-data:www-data /var/www/app

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
        pdo_mysql \
        intl \
        mbstring \
        opcache \
        gd \
        zip \
        bcmath \
        sockets \
        redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/conf/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/conf/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY docker/php/conf/www.conf /usr/local/etc/php-fpm.d/www.conf


# === Development image ===
FROM php-base AS dev

ENV APP_ENV=dev \
    APP_DEBUG=1 \
    COMPOSER_HOME=/tmp/composer

ARG HOST_UID=1000
ARG HOST_GID=1000

RUN install-php-extensions xdebug

COPY docker/php/conf/xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini

RUN groupmod -o -g "${HOST_GID}" www-data \
 && usermod -o -u "${HOST_UID}" -g "${HOST_GID}" www-data \
 && mkdir -p /tmp/composer \
 && chown -R www-data:www-data /tmp/composer

USER www-data

CMD ["php-fpm"]


# === Build image for production ===
FROM php-base AS build

ENV APP_ENV=prod \
    APP_DEBUG=0

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-progress \
    --no-interaction \
    --no-scripts

COPY . ./

RUN composer dump-autoload --classmap-authoritative --no-dev && \
    mkdir -p runtime/logs runtime/cache web/assets && \
    chmod -R 755 runtime web/assets && \
    php yii help


# === Production runtime image ===
FROM php-base AS prod

ENV APP_ENV=prod \
    APP_DEBUG=0

COPY --from=build /var/www/app /var/www/app

RUN chown -R www-data:www-data /var/www/app

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
  CMD SCRIPT_NAME=/fpm-ping SCRIPT_FILENAME=/fpm-ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -q "pong" || exit 1

USER www-data

CMD ["php-fpm"]
