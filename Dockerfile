FROM php:8.0-cli

# Install PHP extensions
RUN apt-get update && apt-get install -y \
        # Composer libs
        git \
        # Zip libs
        libzip-dev \
        # Intl libs
        libicu-dev \
        # GD libs
		libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
    # Configure extensions
    && docker-php-ext-configure bcmath \
    && docker-php-ext-configure zip \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    # Install extensions
    && docker-php-ext-install -j$(nproc) bcmath zip intl gd \
    # Install Xdebug
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    # Install composer
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure XDEBUG
RUN echo 'xdebug.mode=debug' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo 'xdebug.start_with_request=yes' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo 'xdebug.client_host=host.docker.internal' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /var/www/html
CMD [ "php", "-S", "0.0.0.0:8000", "-t", "/var/www/html"]