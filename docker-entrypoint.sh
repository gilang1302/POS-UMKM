#!/bin/sh

# membuat key aplikasi
php artisan key:generate

# membuat database sqlite lalu isi tabel & data
touch database/database.sqlite
php artisan migrate --force --seed

# menjalankan server
php artisan serve --host=0.0.0.0 --port=8000
