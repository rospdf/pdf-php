FROM php:8.0-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    git

# Install PHP extensions + composer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    bcmath \
    zip \
    gd \
    intl \
    xdebug \
    @composer

# Configure XDEBUG
RUN echo 'xdebug.mode=debug' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo 'xdebug.start_with_request=yes' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo 'xdebug.client_host=host.docker.internal' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /var/www/html
CMD [ "php", "-S", "0.0.0.0:8000", "-t", "/var/www/html"]