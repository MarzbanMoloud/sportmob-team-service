FROM php:7.4-fpm
    # Install system dependencies
RUN apt-get -y update && apt-get -y install curl openssl libssl-dev libcurl4-openssl-dev wget zip zlib1g-dev libzip-dev libxml2-dev libfreetype6-dev libjpeg-dev libpng-dev git libssh-dev supervisor \
     && rm -rf /var/lib/apt/lists/* \
     && docker-php-ext-install bcmath zip \
     && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
     && rm -rf /tmp/*

ADD supervisor /etc/supervisor/conf.d/

WORKDIR /usr/share/nginx
