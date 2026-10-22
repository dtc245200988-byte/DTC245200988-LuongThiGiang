FROM php:8.3-cli-alpine
RUN docker-php-ext-install pdo_mysql
WORKDIR /app
USER www-data
EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/app", "/app/router.php"]
