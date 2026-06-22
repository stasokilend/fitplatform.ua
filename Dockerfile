FROM php:8.2-fpm

RUN docker-php-ext-install pdo pdo_mysql
WORKDIR /var/www/html
COPY . /var/www/html
RUN mkdir -p storage/logs storage/uploads && chown -R www-data:www-data storage
