# Используем официальный PHP-образ с Apache
FROM php:8.2-apache

# Устанавливаем системные зависимости (включая PostgreSQL dev)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    nodejs \
    npm \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Устанавливаем composer (если нужно для PHP)
RUN curl -sS https://getcomposer.org/installer | php -- \
    && mv composer.phar /usr/local/bin/composer

# Копируем файлы проекта в контейнер
COPY . /var/www/html/

# Включаем mod_rewrite для Apache
RUN a2enmod rewrite

# Настраиваем права
RUN chown -R www-data:www-data /var/www/html

# Устанавливаем Node.js зависимости, если есть package.json
WORKDIR /var/www/html
RUN if [ -f package.json ]; then npm install; fi

# Открываем порт
EXPOSE 80

# Запускаем Apache
CMD ["apache2-foreground"]
