# TanamYuk API

REST API for TanamYuk, a companion for small urban gardens: kebun, siklus tanam, agenda, jurnal, panen, biaya, and crop recommendations.

## Requirements

- PHP 8.3+
- Composer
- SQLite (default) or another database supported by Laravel 12

## Run

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve
```

Base URL: `http://localhost:8000/api/v1`

The app name is `TanamYuk` (`APP_NAME` in `.env`). Authentication uses a Sanctum bearer token. Send `Authorization: Bearer {token}` on protected routes. Cookie/CSRF stateful auth is not enabled.

## Response shape

```json
{ "success": true, "data": {}, "message": "optional" }
```

```json
{ "success": false, "message": "...", "errors": {} }
```

List endpoints return `data` as an array.

## Demo account

| Email | Password | Profile |
| --- | --- | --- |
| demo@tanamyuk.com | password | Robbi, kebun Kebun Samping Rumah, siklus pakcoy, cabai rawit, and tomat |

Registration creates a Gratis subscription (1 active garden, 3 active cycles) when the free plan exists.

## Endpoints

Public:

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/forgot-password`
- `POST /api/v1/auth/reset-password` (stub)
- `GET /api/v1/assessments/questions`
- `POST /api/v1/assessments`
- `GET /api/v1/assessments/{id}/recommendations`
- `GET /api/v1/commodities`
- `GET /api/v1/commodities/{slug}`
- `GET /api/v1/articles`
- `GET /api/v1/articles/faqs`
- `GET /api/v1/articles/{slug}`
- `GET /api/v1/plans`

Authenticated:

- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`
- `PATCH /api/v1/auth/me`
- `GET /api/v1/subscription`
- `GET|POST /api/v1/gardens`, `GET|PUT|DELETE /api/v1/gardens/{id}`
- `GET|POST /api/v1/cycles`, `GET|PUT /api/v1/cycles/{id}`
- `POST /api/v1/cycles/{id}/activate`
- `POST /api/v1/cycles/{id}/complete`
- The same cycle routes are also available under `/api/v1/planting-cycles`
- `GET|POST /api/v1/tasks`, `GET|PATCH /api/v1/tasks/{id}`
- `POST /api/v1/tasks/{id}/complete`
- `POST /api/v1/tasks/{id}/postpone`
- `POST /api/v1/tasks/{id}/reopen`
- `GET|POST /api/v1/journals`, `PATCH /api/v1/journals/{id}`
- `GET|POST /api/v1/issues`, `PATCH /api/v1/issues/{id}`
- `GET|POST /api/v1/expenses`, `PATCH /api/v1/expenses/{id}`
- `GET|POST /api/v1/harvests`, `PATCH /api/v1/harvests/{id}`
- `GET|POST /api/v1/orders`
- `GET /api/v1/orders/{orderNumber}`
- `GET /api/v1/orders/{orderNumber}/status` (stub)
- `GET /api/v1/dashboard`
- `POST /api/v1/sync/push`
- `GET /api/v1/sync/pull`

Expense categories seeded for `category_code`: `benih`, `media`, `nutrisi`, `pot`, `alat`, `perlindungan`, `lain`.
