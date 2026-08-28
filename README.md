# Kantin Multi-Tenant

Sistem kantin multi-tenant berbasis Laravel.

## Requirements
- PHP 8.4+
- Composer 2.10+
- Node.js 23+ dan npm
- MySQL 8.4+
- Redis

## Setup
1. Clone repository ini
2. Jalankan composer install
3. Jalankan npm install
4. Copy .env.example menjadi .env, lalu isi kredensial database & Redis
5. Jalankan php artisan key:generate
6. Jalankan php artisan migrate --seed

## Run
php artisan serve
npm run dev
Buka http://localhost:8000

## Test
php artisan test
vendor\bin\pint --test
npm run build

## Troubleshooting
- Jika Redis error, pastikan service Redis (via Servbay) sudah berjalan.
- Jika vendor\bin\pint --test gagal, jalankan vendor\bin\pint untuk membenahi otomatis.