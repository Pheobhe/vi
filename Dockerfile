ARG PHP_VERSION=8.1
ARG NGINX_VERSION=1.15

FROM php:${PHP_VERSION}-fpm-alpine AS symfony_php

# persistent / runtime deps
RUN apk add --update --no-cache \
		acl \
		file \
		gettext \
		mariadb-client \
		freetype libpng libjpeg-turbo\
	;

ARG APCU_VERSION=5.1.21
RUN set -eux; \
	apk add --update --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		coreutils \
		freetype-dev \
		icu-dev \
		libjpeg-turbo-dev \
		freetype-dev \
		libpng-dev \
		tzdata \
		libtool \
		libwebp-dev \
		libxml2-dev \
		libzip-dev \
		mariadb-dev \
		zlib-dev \
	; \
	\
	docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp; \
	docker-php-ext-install -j$(nproc) \
		exif \
		gd \
		soap \
		intl \
		pdo_mysql \
		zip \
	; \
	pecl install \
		apcu-${APCU_VERSION} \
	; \
	pecl clear-cache; \
	docker-php-ext-enable \
		apcu \
		soap \
		opcache \
	; \
	runDeps="$( \
		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
			| tr ',' '\n' \
			| sort -u \
			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --update --no-cache --virtual .symfony-phpexts-rundeps $runDeps; \
	apk del .build-deps

RUN wget -O /usr/local/bin/php-fpm-healthcheck \
  https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
  && chmod +x /usr/local/bin/php-fpm-healthcheck

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/php.ini
# COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf:cached

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/symfony

# build for production
ARG APP_ENV=dev

# prevent the reinstallation of vendors at every changes in the source code
COPY composer.json composer.lock ./
RUN set -eux; \
	composer install --prefer-dist --no-scripts --no-progress --no-suggest \
	&& composer dump-autoload --optimize
# agregar un --no-dev en el composer para pasarlo a prod
# copy only specifically what we need
COPY .env ./
COPY public public/
COPY src src/
COPY bin bin/
COPY migrations migrations/ 
COPY config config/
COPY templates templates/ 
COPY tests tests/ 
COPY phpunit.xml ./ 

RUN set -xe && echo "pm.status_path = /status" >> /usr/local/etc/php-fpm.d/zz-docker.conf

ENV TZ=America/Argentina/Buenos_Aires
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

COPY docker/php/docker-entrypoint /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint; 

ENTRYPOINT ["docker-entrypoint"]

CMD ["php-fpm"]


FROM nginx:${NGINX_VERSION}-alpine AS symfony_nginx

RUN apk add --update --no-cache tzdata;

WORKDIR /srv/symfony
ARG APP_ENV=dev
# COPY docker/nginx/conf.d/${}.conf /etc/nginx/conf.d/default.conf
COPY docker/nginx/dev.conf /etc/nginx/conf.d/default.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf

ENV TZ=America/Argentina/Buenos_Aires
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
COPY public ./
