#!/bin/sh
set -e

# If the vendor volume is empty, populate it from the build cache
if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "Vendor volume is empty. Copying packages from build cache..."
    cp -a /tmp/vendor-cache/. /var/www/html/vendor/
    echo "Vendor packages copied successfully."
fi

# Fix storage permissions for www-data
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create .env from .env.example if missing
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env
fi

# Generate app key if not set
if ! grep -q '^APP_KEY=base64:' /var/www/html/.env; then
    php artisan key:generate --force
fi

exec "$@"
