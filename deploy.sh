set -e

cd /var/www/ebook-authentication-service

git pull origin main

composer install --no-dev --optimize-autoloader

php artisan migrate --force

php artisan optimize:
php artisan config:cache
php artisan route:cache

sudo systemctl reload php8.3-fpm

# supervisorctl reread
# supervisorctl update
# supervisorctl restart ebook-authentication-queue
# supervisorctl restart ebook-authentication-listener
