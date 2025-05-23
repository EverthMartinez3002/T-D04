FROM composer:2 AS builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-interaction

COPY . .

FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
      mariadb-dev \
      oniguruma-dev \
    && docker-php-ext-install pdo_mysql mbstring \
    && apk del mariadb-dev oniguruma-dev \
    && rm -rf /var/cache/apk/*

RUN addgroup -g 1000 appgroup \
 && adduser -D -u 1000 -G appgroup appuser

WORKDIR /var/www/html

COPY --from=builder /app /var/www/html

RUN chown -R appuser:appgroup /var/www/html

EXPOSE 9000

USER appuser

CMD ["php-fpm"]
