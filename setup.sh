#!/bin/bash
set -e

# Update packages
apt-get update

# Install PHP CLI (Codex seems to already have it, but doesn't hurt to ensure)
apt-get install -y php-cli php-mbstring unzip curl git

# Optionally install WP CLI or Composer
# curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
# chmod +x wp-cli.phar
# mv wp-cli.phar /usr/local/bin/wp

# Confirm installed PHP version
php -v
