# Chạy website giống Laragon: Apache + PHP 8.3, web root là thư mục gốc dự án.
# index.php và .htaccess ở gốc chuyển request vào ứng dụng trong legacy/.

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist --ignore-platform-reqs
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts

FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd zip \
    && a2enmod rewrite headers \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf 'upload_max_filesize=20M\npost_max_size=25M\nmemory_limit=256M\ndate.timezone=Asia/Ho_Chi_Minh\n' > "$PHP_INI_DIR/conf.d/app.ini"

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
RUN mkdir -p legacy/storage legacy/logs public/uploads legacy/public/uploads \
    && chown -R www-data:www-data legacy/storage legacy/logs public/uploads legacy/public/uploads storage

# Railway cấp cổng qua biến PORT. mod_php chỉ chạy với mpm_prefork: tắt các MPM khác
# ngay lúc khởi động để tránh lỗi "More than one MPM loaded".
CMD rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* \
    && a2enmod -q mpm_prefork \
    && sed -i "s/^Listen .*/Listen ${PORT:-80}/" /etc/apache2/ports.conf \
    && sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${PORT:-80}>/" /etc/apache2/sites-available/000-default.conf \
    && apache2-foreground
