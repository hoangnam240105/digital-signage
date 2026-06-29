FROM php:8.2-fpm-alpine

# Cài đặt các thư viện hệ thống cần thiết (đã thêm icu-dev và libzip-dev)
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    icu-dev \
    libzip-dev

# Cài đặt các extension PHP cần thiết cho Filament (đã thêm intl và zip)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath xml gd intl zip

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy toàn bộ code vào trong Docker
COPY . .

# Cài đặt các thư viện PHP qua Composer
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Cấp quyền cho thư mục storage của Laravel
RUN chmod -R 775 storage bootstrap/cache

# Cấu hình Nginx gọn nhẹ
RUN echo 'server { \
    listen 80; \
    root /var/www/html/public; \
    index index.php index.html; \
    location / { try_files $uri $uri/ /index.php?$query_string; } \
    location ~ \.php$ { \
    try_files $uri =404; \
    fastcgi_split_path_info ^(.+\.php)(/.+)$; \
    fastcgi_pass 127.0.0.1:9000; \
    fastcgi_index index.php; \
    include fastcgi_params; \
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_path_info; \
    } \
    }' > /etc/nginx/http.d/default.conf

# Chạy cả PHP-FPM và Nginx khi khởi động
CMD php-fpm -D && nginx -g "daemon off;"