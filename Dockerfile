FROM php:7.4-cli
RUN apt-get update && apt-get install -y libyaml-dev
RUN pecl install yaml
RUN docker-php-ext-enable yaml

