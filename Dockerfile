FROM php:8.4.3-cli-bookworm AS os
LABEL "app"="essa"

RUN apt-get update && apt-get install -y git \
    libzip-dev unzipa
RUN docker-php-ext-install -j$(nproc) zip \
 && docker-php-source delete

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
#RUN curl -fsS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
# && ls -la /usr/local/bin

WORKDIR /build
RUN useradd -u 1000 -g 0 -m -s /bin/bash app \
 && chown -R app:root /build

USER app
#COPY composer.json .
#RUN composer install --no-interaction --prefer-dist --no-autoloader --no-scripts

FROM os AS app-dev
WORKDIR /app
ENTRYPOINT ["./entrypoint.sh"]
CMD ["tail", "-f", "/dev/null"]

FROM os AS app-test
WORKDIR /app
COPY . .
RUN composer dump-autoload
