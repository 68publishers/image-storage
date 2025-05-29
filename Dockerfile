FROM php:8.1.12-cli-alpine3.16 AS php81

CMD ["/bin/sh"]
WORKDIR /var/www/html

RUN apk add --no-cache --update git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN set -ex \
    # Build dependencies
    && apk add --no-cache --virtual .build-deps  \
        $PHPIZE_DEPS \
    && apk add --no-cache \
    	libgomp \
        freetype-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        libpng-dev \
    	libavif-dev \
        imagemagick \
        imagemagick-dev \
    && pecl install imagick-3.7.0 \
    && pecl install pcov \
    && pecl install uopz-7.1.1 \
    && docker-php-ext-enable \
        imagick pcov uopz \
    && apk del .build-deps

CMD tail -f /dev/null

FROM php:8.2.21-cli-alpine3.20 AS php82

CMD ["/bin/sh"]
WORKDIR /var/www/html

RUN apk add --no-cache --update git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN set -ex \
    # Build dependencies
    && apk add --no-cache --virtual .build-deps  \
        $PHPIZE_DEPS \
    && apk add --no-cache \
    	libgomp \
        freetype-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        libpng-dev \
    	libavif-dev \
        imagemagick \
        imagemagick-dev \
    && pecl install imagick-3.7.0 \
    && pecl install pcov \
    && pecl install uopz-7.1.1 \
    && docker-php-ext-enable \
        imagick pcov uopz \
    && apk del .build-deps

CMD tail -f /dev/null

FROM php:8.3.9-cli-alpine3.20 AS php83

CMD ["/bin/sh"]
WORKDIR /var/www/html

RUN apk add --no-cache --update git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN set -ex \
    # Build dependencies
    && apk add --no-cache --virtual .build-deps  \
        $PHPIZE_DEPS \
    && apk add --no-cache \
    	libgomp \
        freetype-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        libpng-dev \
    	libavif-dev \
        imagemagick \
        imagemagick-dev \
    && pecl install imagick-3.8.0 \
    && pecl install pcov \
    && pecl install uopz-7.1.1 \
    && docker-php-ext-enable \
        imagick pcov uopz \
    && apk del .build-deps

CMD tail -f /dev/null

FROM php:8.4.4-cli-alpine3.21 AS php84

CMD ["/bin/sh"]
WORKDIR /var/www/html

RUN apk add --no-cache --update git
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN set -ex \
    # Build dependencies
    && apk add --no-cache --virtual .build-deps  \
        $PHPIZE_DEPS \
    && apk add --no-cache \
    	libgomp \
        freetype-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        libpng-dev \
    	libavif-dev \
        imagemagick \
        imagemagick-dev \
    && pecl install imagick-3.8.0 \
    && pecl install pcov \
    && mkdir -p /usr/src/php/ext/uopz \
    && curl -fsSL https://github.com/zonuexe/uopz/archive/refs/heads/support/php84-exit.tar.gz | tar xvz -C /usr/src/php/ext/uopz --strip 1 \
    && docker-php-ext-install uopz \
    && docker-php-ext-enable \
        imagick pcov uopz \
    && apk del .build-deps

CMD tail -f /dev/null
