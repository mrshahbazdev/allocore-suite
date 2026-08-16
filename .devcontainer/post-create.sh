#!/bin/bash
set -e

echo "Setting up Allocore Suite dev container..."

# Ensure required PHP extensions are available
if command -v install-php-extensions >/dev/null 2>&1; then
    sudo install-php-extensions pdo_mysql zip gd intl bcmath mbstring exif pcntl opcache
elif command -v docker-php-ext-install >/dev/null 2>&1; then
    sudo apt-get update
    sudo apt-get install -y --no-install-recommends \
        libzip-dev libonig-dev libpng-dev libjpeg-dev libfreetype6-dev libicu-dev libxml2-dev
    sudo docker-php-ext-configure gd --with-freetype --with-jpeg || true
    sudo docker-php-ext-install -j"$(nproc)" pdo_mysql zip gd intl bcmath mbstring exif pcntl opcache
fi

# Install Composer if missing
if ! command -v composer >/dev/null 2>&1; then
    curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
fi

# Install VS Code `code` CLI
bash .devcontainer/install-code-cli.sh

# Install PHP and JS dependencies
composer install --no-interaction
npm install

# Bootstrap environment if no .env exists
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

echo "Allocore Suite dev container ready. Run 'php artisan serve' to start."
