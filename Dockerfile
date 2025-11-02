# Используем PHP + Apache
FROM php:8.2-apache

# Устанавливаем расширение PostgreSQL
RUN docker-php-ext-install pgsql pdo pdo_pgsql

# Копируем все файлы в /var/www/html
COPY . /var/www/html/

# Устанавливаем права
RUN chown -R www-data:www-data /var/www/html

# Открываем порт
EXPOSE 80

# Запускаем Apache
CMD ["apache2-foreground"]
