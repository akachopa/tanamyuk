# TanamYuk

SaaS manajemen budidaya pekarangan (PWA mobile-first) untuk https://tanamyuk.com.

Stack: **Laravel 12** (API) + **Vue 3** + **Vite** + **Pinia** + **PWA**. Layout UI mengikuti desain [tanamyuk_html](https://github.com/akachopa/tanamyuk_html).

## Fitur MVP (fondasi)

- Landing page + asesmen rekomendasi komoditas (rule-based)
- Registrasi / login (Sanctum bearer token)
- Onboarding, beranda, kebun, agenda, catatan, akun
- Lahan, siklus tanam, agenda dari template, jurnal, biaya, panen, masalah
- Paket Gratis / Plus Bulanan / Plus Tahunan
- Order pembayaran (stub Tripay: QRIS/VA UI)
- FAQ/panduan, sync push/pull stub, PWA installable

## Persyaratan

- PHP 8.3+, Composer
- Node.js 20+
- SQLite (default) atau PostgreSQL

## Menjalankan lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
npm install
npm run build
php artisan serve
```

Buka `http://localhost:8000`.

Untuk development frontend dengan HMR:

```bash
npm run dev
# terminal lain
php artisan serve
```

## Akun demo

| Email | Password |
| --- | --- |
| demo@tanamyuk.com | password |

Profil demo: Robbi, Kebun Samping Rumah, siklus Pakcoy / Cabai Rawit / Tomat.

## API

Base URL: `/api/v1`

Respons sukses: `{ "success": true, "data": ... }`  
Auth: `Authorization: Bearer {token}`

Endpoint utama: auth, assessments, gardens, cycles, tasks, journals, issues, expenses, harvests, plans, orders, dashboard, sync, commodities, articles.

## Navigasi aplikasi

1. Beranda  
2. Kebun  
3. Agenda  
4. Catatan  
5. Akun  

## Catatan implementasi

- Desain memakai CSS dari layout HTML TanamYuk (`resources/css/tanamyuk.css`), bukan Tailwind untuk UI utama.
- Tripay callback, offline IndexedDB penuh, dan panel super admin masih tahap berikutnya sesuai roadmap plan.
