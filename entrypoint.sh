#!/usr/bin/env bash

#cd /var/www/html
echo "configuration started"
echo "composer install"
echo $(composer install)
echo "php artisan key:generate"
echo $(php artisan key:generate)
echo "php artisan migrate"
echo $(php artisan migrate)
echo "configuration finished"
echo "apache2 start"
/usr/sbin/apache2ctl -D FOREGROUND

