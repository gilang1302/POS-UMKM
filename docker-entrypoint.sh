#!/bin/sh

# lokasi file database sqlite (bisa diganti lewat docker-compose)
DB_FILE=${DB_DATABASE:-database/database.sqlite}

# catat lokasi database ke .env supaya server php ikut memakainya
if [ -n "$DB_DATABASE" ]; then
    echo "DB_DATABASE=$DB_DATABASE" >> .env
fi

# membuat key aplikasi
php artisan key:generate --force

# membuat file database kalau belum ada
mkdir -p "$(dirname "$DB_FILE")"
touch "$DB_FILE"

# buat tabel, lalu isi data contoh (seeder tidak mengisi ulang kalau data sudah ada)
php artisan migrate --force --seed

# menjalankan server
php artisan serve --host=0.0.0.0 --port=8000
