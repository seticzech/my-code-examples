FROM php:7.3-fpm

ARG DOCKER_USER_ID=1000
ARG DOCKER_GROUP_ID=1000

RUN if [ ${DOCKER_USER_ID:-0} -ne 0 ] && [ ${DOCKER_GROUP_ID:-0} -ne 0 ]; then \
    userdel -f www-data &&\
    if getent group www-data ; then groupdel www-data; fi &&\
    groupadd -g ${DOCKER_GROUP_ID} www-data &&\
    useradd -l -u ${DOCKER_USER_ID} -g www-data www-data &&\
    install -d -m 0755 -o www-data -g www-data /home/www-data &&\
    chown --changes --silent --no-dereference --recursive \
          --from=33:33 ${DOCKER_USER_ID}:${DOCKER_GROUP_ID} \
        /home/www-data \
;fi
        
RUN apt-get update -y && apt-get install -y \
        libfreetype6-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libxml2-dev \
        libpq-dev \
        git \
        unzip \
        autoconf \
        openssl \
        postgresql-client \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd bcmath sockets mbstring xml pdo pdo_pgsql

# Install and enable xDebug
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER www-data

#COPY php.ini /usr/local/etc/php/conf.d/php-project.ini
