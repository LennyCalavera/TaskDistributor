FROM php:8.0-apache

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-enabled/000-default.conf

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN apt-get update

RUN apt-get install -y libpq-dev \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "\n xdebug.mode=develop,debug\n \
              xdebug.start_with_request=yes\n \
              xdebug.client_host=host.docker.internal\n \
              xdebug.client_port=9003\n" >> "$PHP_INI_DIR/php.ini"

RUN a2enmod rewrite
RUN service apache2 restart

COPY entrypoint.sh ./entrypoint.sh

CMD ["/bin/sh", "-c", "./entrypoint.sh"]

