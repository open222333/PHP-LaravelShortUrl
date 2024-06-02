# PHP-LaravelShortUrl

```bash
cd yourls

# 安裝composer.json內紀錄的框架所需套件
composer install

# 將資源複製到指定的發佈位置
php artisan vendor:publish --provider="Encore\Admin\AdminServiceProvider"

# 透過artisan產生一組網站專屬密鑰
php artisan key:generate

# php內置 web環境
php artisan serve
```