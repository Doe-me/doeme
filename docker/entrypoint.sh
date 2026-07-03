#!/bin/sh
set -e

if [ ! -f /var/www/vendor/autoload.php ]; then
    echo "[entrypoint] vendor/autoload.php not found — running composer install..."
    composer install --no-dev --optimize-autoloader --working-dir=/var/www
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
