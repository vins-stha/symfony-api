FROM php:8.0.10-fpm
RUN apt-get -y update \
  && apt-get install -y libicu-dev libzip-dev zip \
  && docker-php-ext-configure intl \
  && docker-php-ext-install intl \
  && docker-php-ext-install opcache \
  && docker-php-ext-enable opcache \
  && docker-php-ext-install pdo_mysql \
  && docker-php-ext-install zip
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN curl -sS https://get.symfony.com/cli/installer | bash \
 && mv /root/.symfony/bin/symfony /usr/local/bin/symfony
WORKDIR /var/www/symfony
COPY . .