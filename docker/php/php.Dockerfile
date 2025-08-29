# PHP 8.4 FPM with common Laravel extensions
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y     git zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev libicu-dev libpq-dev     && docker-php-ext-configure intl     && docker-php-ext-install pdo pdo_mysql zip gd intl opcache     && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/sistema_de_agendamento

# PHP overrides
COPY ./docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
