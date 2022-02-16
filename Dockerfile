FROM php:8.1-cli

RUN apt-get update

# ZIP
RUN apt-get install -y \
        libzip-dev \
        zlib1g-dev \
        zip \
	&& docker-php-ext-install zip

# git
RUN apt-get install -y git

# mysqli
RUN docker-php-ext-install mysqli \
	&& docker-php-ext-enable mysqli

# redis
RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis
ADD .docker/php/docker-php-ext-redis.ini /usr/local/etc/php/conf.d/docker-php-ext-redis.ini

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
	&& chmod 755 /usr/bin/composer

# pcntl
RUN docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl

# pecl/ev
RUN pecl install -o -f ev \
    && docker-php-ext-enable ev

# JIT
ADD .docker/php/docker-php-enable-jit.ini /usr/local/etc/php/conf.d/docker-php-enable-jit.ini

RUN apt-get clean

COPY ./composer.json /app/
COPY ./index.php /app/
COPY ./src/ /app/src/

WORKDIR /app

RUN composer install

CMD [ "php", "./index.php" ]
