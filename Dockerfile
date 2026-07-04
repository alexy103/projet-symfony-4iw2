FROM php:8.4-apache

RUN apt-get update && \
    apt-get install -y git libpq-dev unzip libzip-dev libicu-dev && \
    docker-php-ext-install pdo_pgsql zip intl && \
    rm -rf /var/lib/apt/lists/*

RUN echo "date.timezone=Europe/Paris" > /usr/local/etc/php/conf.d/timezone.ini

# Installer Composer depuis l'image officielle Composer
COPY --from=composer:lts /usr/bin/composer /usr/bin/composer

# Installer symfony/cli
COPY --link \
    --from=ghcr.io/symfony-cli/symfony-cli:latest \
    /usr/local/bin/symfony /usr/local/bin/symfony

# Dire à Apache que la racine web est /var/www/html/app/public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/app/public

# Remplacer /var/www/html par /var/www/html/app/public dans la config Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && a2enmod rewrite

WORKDIR /var/www/html/app