FROM php:7.4-fpm

LABEL maintainer="Bahadır Birsöz <github.com/bahadirbirsoz>"

ARG PSR_VERSION=1.0.0
ARG PHALCON_VERSION=4.0.5
ARG PHALCON_EXT_PATH=php7/64bits

RUN set -xe && \
        # Download PSR, see https://github.com/jbboehr/php-psr
        curl -LO https://github.com/jbboehr/php-psr/archive/v${PSR_VERSION}.tar.gz && \
        tar xzf ${PWD}/v${PSR_VERSION}.tar.gz && \
        # Download Phalcon
        curl -LO https://github.com/phalcon/cphalcon/archive/v${PHALCON_VERSION}.tar.gz && \
        tar xzf ${PWD}/v${PHALCON_VERSION}.tar.gz && \
        docker-php-ext-install -j $(getconf _NPROCESSORS_ONLN) \
            ${PWD}/php-psr-${PSR_VERSION} \
            ${PWD}/cphalcon-${PHALCON_VERSION}/build/${PHALCON_EXT_PATH} \
        && \
        # Remove all temp files
        rm -r \
            ${PWD}/v${PSR_VERSION}.tar.gz \
            ${PWD}/php-psr-${PSR_VERSION} \
            ${PWD}/v${PHALCON_VERSION}.tar.gz \
            ${PWD}/cphalcon-${PHALCON_VERSION} \
        && \
        php -m


# Install packages
RUN apt-get update && \
    apt-get install -y zlib1g-dev && \
    apt-get install -y libpng-dev && \
    apt-get install nano && \
    apt-get install -y libzip-dev && \
    apt-get install -y apt-utils && \
     apt-get install -y libicu-dev && \
     apt-get install -y unzip && \
     apt-get install -y libxml2-dev && \
     docker-php-ext-enable psr && \
     docker-php-ext-configure intl && \
     docker-php-ext-install intl && \
     docker-php-ext-install soap  && \
     docker-php-ext-install zip && \
     docker-php-ext-install gd && \
     docker-php-ext-install pdo pdo_mysql

WORKDIR /app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#RUN composer global require phalcon/devtools
#RUN composer require --dev phalcon/ide-stubs

#RUN ln -s /root/.composer/vendor/bin/phalcon.php /usr/local/bin/phalcon && chmod ugo+x /usr/local/bin/phalcon

COPY composer.json .
COPY composer.lock .

ADD src src
ADD tests tests

RUN composer install

RUN pecl install xdebug
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN chmod +x $PWD/vendor/bin/*

#RUN composer global require phalcon/devtools

RUN ln -s $PWD/vendor/bin/* /usr/local/bin/

RUN composer dump-autoload

RUN apt-get clean -y

ENTRYPOINT ["/usr/local/bin/phpunit"]