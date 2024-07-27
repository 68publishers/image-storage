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
    && docker-php-ext-enable \
        imagick \
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
    && docker-php-ext-enable \
        imagick \
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
    && pecl install imagick-3.7.0 \
    && docker-php-ext-enable \
        imagick \
    && apk del .build-deps

CMD tail -f /dev/null
