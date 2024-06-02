#!/bin/bash
set -e

# 等待 MySQL 服務器啟動
# echo "等待 MySQL 服務器啟動..."
# while ! mysqladmin ping -h"$DB_HOST" --silent; do
#     sleep 1
# done

# 運行遷移命令
php artisan migrate --force
php artisan admin:install --force

# 启动 PHP-FPM
php-fpm
