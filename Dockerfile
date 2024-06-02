# 使用 PHP 官方映像作為基礎映像
FROM php:7.4-fpm

# 安裝常用工具和依賴庫
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo_mysql zip

# 安裝 Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 設置工作目錄
WORKDIR /var/www/html
# 複製 Laravel 專案文件到工作目錄
COPY ./yourls /var/www/html

# 安裝 Laravel 依賴
RUN composer install
RUN composer install --no-scripts --no-autoloader
RUN composer dump-autoload --optimize
# 自動確認目前系統環境是否可使用這個套件
RUN composer require encore/laravel-admin
# 運行下面的命令來發布資源：
RUN php artisan vendor:publish --provider="Encore\Admin\AdminServiceProvider"

# 設置文件權限
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 複製入口點腳本
# COPY docker-entrypoint.sh /usr/local/bin/
# RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# 設置入口點腳本
# ENTRYPOINT ["docker-entrypoint.sh"]

RUN php artisan migrate --force
RUN php artisan admin:install --force
