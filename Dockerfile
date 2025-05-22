FROM php:8.1-cli

# Cài đặt các phần mở rộng cần thiết
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    curl \
    && docker-php-ext-install curl

# Sao chép mã nguồn vào container
COPY . /var/www/html
WORKDIR /var/www/html

# Khởi động server PHP
CMD ["php", "-S", "0.0.0.0:10000"]
