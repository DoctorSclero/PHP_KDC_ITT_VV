FROM php:8.0-apache

# Install xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug 

# Enable apache mod rewrite
RUN a2enmod rewrite

# Enable mysqli
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy extension configuration files to the container
ADD config /usr/local/etc/php/conf.d

# Update and upgrade packages and dependencies
RUN apt update && apt upgrade -y
