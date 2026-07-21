# Examly API

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-cache%20%2B%20queue-DC382D?style=flat&logo=redis&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)

Laravel 13 API powering **Examly** — build an exam (multiple choice, text, checkbox, file upload questions), share it via a short link, and collect verified responses from anonymous visitors — no signup required, just a one-time email code.

Frontend lives in a separate repo: `examly-web` (Next.js 16).

## Stack

- **Laravel 13** (PHP 8.3), **Sanctum** for auth (cookie for web, token-ready for future mobile)
- **MySQL 8** — primary datastore
- **Redis** — public exam page caching, rate limiting, OTP codes (cache, not a table), and queue driver
- **Pest** — testing

## Core concepts

- **Exam** — creator-owned, has a public slug, a description, and ordered questions
- **Question** — one table, polymorphic by `type` (`multiple_choice`, `checkbox`, `text`, `file_upload`), type-specific config stored as JSON
- **Attempt** — one verified email = one attempt per exam (no resume, no retake in v1)
- **Answer** — one response per question per attempt

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate

# set DB_* and REDIS_* in .env, then:
php artisan migrate

php artisan serve
```

## Status

🚧 Early development.

## License

MIT
