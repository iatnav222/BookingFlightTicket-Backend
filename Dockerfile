FROM php:8.2-apache

# Cài đặt các thư viện lõi và extension MySQL cho PHP
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip \
    && docker-php-ext-install pdo_mysql zip

# Bật mod_rewrite của Apache (Bắt buộc cho Laravel routing)
RUN a2enmod rewrite

# Cấu hình Apache trỏ thẳng vào thư mục /public của Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# --- DÒNG THÊM MỚI BẮT ĐẦU TỪ ĐÂY ---
# Ép Apache lắng nghe trên cổng động do Render cấp ($PORT)
RUN sed -s -i -e "s/80/\${PORT}/" /etc/apache2/ports.conf
RUN sed -s -i -e "s/VirtualHost \*:80/VirtualHost \*:\${PORT}/" /etc/apache2/sites-available/000-default.conf
# --- KẾT THÚC DÒNG THÊM MỚI ---

# Copy toàn bộ code của bạn vào container
COPY . /var/www/html

# Cài đặt Composer và chạy lệnh cài thư viện
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Cấp quyền đọc/ghi cho Laravel để không bị lỗi Permission Denied
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache