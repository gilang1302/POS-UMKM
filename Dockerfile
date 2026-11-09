FROM php:8.3-cli

# install git & unzip
RUN apt-get update && apt-get install -y git unzip

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
RUN chmod -R 777 storage bootstrap/cache

# port laravel
EXPOSE 8000

# jalankan script
CMD ["sh", "docker-entrypoint.sh"]
