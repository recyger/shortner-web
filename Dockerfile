FROM php:7.2-alpine

LABEL maintainer="recyger <recyger@gmail.com>"

# INSTALL PHP
RUN docker-php-ext-install pdo pdo_mysql

# INSTALL COMPOSER
ENV COMPOSER_ALLOW_SUPERUSER 1

RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
  && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
  && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
  && php /tmp/composer-setup.php --install-dir=/bin --filename=composer

# SETUP
EXPOSE 8080
COPY ./bin/entrypoint.sh /usr/src/api/bin/entrypoint.sh
WORKDIR /usr/src/api
RUN chmod +x bin/entrypoint.sh

# RUN SERVER
ENTRYPOINT [ "sh", "bin/entrypoint.sh" ]
