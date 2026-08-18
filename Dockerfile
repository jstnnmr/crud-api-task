FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    curl \
    git \
    && docker-php-ext-install pdo_mysql mbstring opcache \
    && docker-php-ext-configure zip && docker-php-ext-install zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN set -eux \
    && pecl install -o -f redis \
    && docker-php-ext-enable redis

RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY nginx.conf /etc/nginx/nginx.conf
COPY supervisord.conf /etc/supervisord.conf
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker-entrypoint.sh"]
