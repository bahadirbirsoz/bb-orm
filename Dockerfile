FROM php:7.4-fpm

LABEL maintainer="Bahadır Birsöz <github.com/bahadirbirsoz>"


RUN apt-get update
RUN apt-get install -y zlib1g-dev
RUN apt-get install -y libpng-dev
RUN apt-get install -y libzip-dev
RUN apt-get install -y unzip
RUN docker-php-ext-install gd
RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get install git -y
RUN pecl install xdebug
WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY composer.json .
COPY composer.lock .
COPY phpunit.xml phpunit.xml
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

ADD src src
ADD tests tests

RUN composer install

RUN chmod +x $PWD/vendor/bin/*

RUN ln -s $PWD/vendor/bin/* /usr/local/bin/

RUN composer dump-autoload

RUN apt-get clean -y

ENTRYPOINT ["/usr/local/bin/phpunit"]