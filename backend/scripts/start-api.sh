#!/bin/sh
set -eu
composer install --no-interaction --prefer-dist --no-progress
php -r "file_exists('.env') || copy('.env.example', '.env'); file_exists('database/database.sqlite') || touch('database/database.sqlite');"
if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --no-interaction
fi
php artisan migrate --seed --force --no-interaction
exec php artisan serve --host=0.0.0.0 --port=8000
