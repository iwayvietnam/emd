#!/bin/bash
set -e

export PATH=/usr/local/bin:/usr/local/sbin:/bin:/sbin:/usr/bin:/usr/sbin:/usr/X11R6/bin

APP_ENV=${APP_ENV:-local}

php /var/www/html/artisan serve --host=0.0.0.0 --port=80 >/dev/null 2>&1
php /var/www/html/artisan queue:work --env=$APP_ENV >/dev/null 2>&1
php /var/www/html/artisan policy:listen start >/dev/null 2>&1
