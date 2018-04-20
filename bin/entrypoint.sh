#!/usr/bin/env sh

composer install --optimize-autoloader
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration
php -S 0.0.0.0:8080 -t public