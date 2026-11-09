FROM php:8.3-cli

# install git, unzip, dan library untuk ekstensi zip (dipakai export Excel)
RUN apt-get update && apt-get install -y git unzip libzip-dev \
    && docker-php-ext-install zip pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# folder kerja
WORKDIR /var/www/html

# copy semua file project
COPY . .

# install library laravel
RUN composer install --no-interaction --no-scripts

# buat file .env
RUN cp .env.example .env

# izin folder storage
RUN chmod -R 777 storage bootstrap/cache database

# server bawaan php dibuat multi-worker supaya beberapa kasir bisa transaksi bersamaan
ENV PHP_CLI_SERVER_WORKERS=4

# port laravel
EXPOSE 8000

# jalankan script
CMD ["sh", "docker-entrypoint.sh"]
