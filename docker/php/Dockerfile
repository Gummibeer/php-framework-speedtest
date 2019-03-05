FROM php:7.3-fpm

ENV COMPOSER_MEMORY_LIMIT -1
ENV LANG en_US.UTF-8

RUN apt-get update -qq
RUN apt-get install -y git \
                       zip \
                       unzip \
                       curl \
                       ca-certificates \
                       libzip-dev

# Install composer
ARG GITHUB_OAUTH_TOKEN
RUN php -r "copy('https://getcomposer.org/composer.phar', '/usr/local/bin/composer');" \
    && chmod ugo+x /usr/local/bin/composer \
    && composer self-update \
    && composer config -g github-oauth.github.com $GITHUB_OAUTH_TOKEN

RUN pecl install zip

RUN docker-php-ext-enable zip

CMD ["php-fpm"]
