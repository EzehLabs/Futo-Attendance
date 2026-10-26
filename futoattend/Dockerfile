FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html
COPY . /var/www/html

EXPOSE 80
