FROM ubuntu:24.04

# Prevent interactive prompts during installation
ENV DEBIAN_FRONTEND=noninteractive

# Install PHP and required extensions
RUN apt-get update && apt-get install -y \
    php8.3-cli \
    php8.3-pgsql \
    php8.3-xml \
    php8.3-mbstring \
    php8.3-zip \
    php8.3-curl \
    php8.3-dom \
    php8.3-xdebug \
    curl \
    unzip \
    git

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www/html

# Create directory for Composer cache
RUN mkdir -p /var/www/.composer && chown -R www-data:www-data /var/www/.composer

# Keep container running
CMD ["tail", "-f", "/dev/null"]
