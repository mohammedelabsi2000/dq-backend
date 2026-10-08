#!/bin/sh
set -e

cd /var/www/html

if [ -z "$APP_KEY" ]; then
    echo "APP_KEY is not set. Generate one with: php artisan key:generate --show"
    exit 1
fi

# The platform tells the container which port to listen on
sed "s/__PORT__/${PORT:-10000}/" /etc/nginx/nginx.render.conf.template > /etc/nginx/http.d/default.conf

# Fix storage permissions for www-data
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Uploaded files are served from public/storage
php artisan storage:link || true

# PHP-FPM workers run as www-data and may not be able to read a mounted secret file
if [ -n "$MYSQL_ATTR_SSL_CA" ] && [ -f "$MYSQL_ATTR_SSL_CA" ]; then
    cp "$MYSQL_ATTR_SSL_CA" /etc/ssl/mysql-ca.pem
    chmod 644 /etc/ssl/mysql-ca.pem
    export MYSQL_ATTR_SSL_CA=/etc/ssl/mysql-ca.pem
fi

# Cache with the runtime environment variables
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

# QuranSeeder fetches every verse from a remote API and inserts row by row, which
# outlasts the platform's deploy timeout; load the same reference data from a dump
php artisan tinker --execute="if (!DB::table('quran_surahs')->exists()) { foreach (file(database_path('data/quran.sql')) as \$line) { if (trim(\$line) !== '') { DB::unprepared(\$line); } } echo 'Quran data loaded'; }"

# The seeders are idempotent, but UserSeeder resets the admin password every run
if [ "$RUN_SEEDERS" = "true" ]; then
    php artisan db:seed --force
fi

# Artisan ran as root above; hand any files it created back to www-data
chown -R www-data:www-data storage bootstrap/cache

exec "$@"
