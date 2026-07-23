![Examly API Hero Banner](public/hero.jpg)

# Examly API

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=flat&logo=php&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-cache%20%2B%20queue-DC382D?style=flat&logo=redis&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white)

**Examly** is a SaaS exam platform that enables creators to easily build, share, and collect responses for online exams. The backend is a clean, well-structured **Laravel 13 API**.

**Key Highlights:**

- Creators build exams with multiple question types (Multiple Choice, Checkbox, Text, File Upload)
- Anonymous exam taking with **email OTP verification** (no user account required)
- Short-link sharing
- Resume support for interrupted attempts
- Secure owner dashboard with full result management

Frontend: Next.js application (`examly-web`)

---

## Features

- Full exam lifecycle management (draft ↔ published)
- Four question types with flexible configuration stored as JSON
- Secure anonymous participation via one-time email code
- Ability to resume exams at any point
- Comprehensive owner dashboard APIs (results, attempts, file downloads)
- Rate-limited OTP system with Redis
- File upload answer handling with secure storage

---

## Architecture

- Owner authentication uses Laravel Sanctum.
- Public participants authenticate through OTP verification and attempt tokens.
- Answers are stored per question to support resume flows.
- Redis handles OTP storage, queues, and rate limiting.
- File uploads are stored separately with authorized downloads.

---

## Core Domain Models

- **Exam** — Owned by a `User`. Supports `draft`/`published` status, slug, and `published_at`.
- **Question** — Belongs to an Exam. Supports different question types through a `type` discriminator (`multiple_choice`, `checkbox`, `text`, `file_upload`). Options and config are stored as JSON.
- **Attempt** — One per (Exam + taker email). Verified via OTP. Uses a random `attempt_token` (passed via `X-Attempt-Token` header) for subsequent requests — no Sanctum session for guests.
- **Answer** — One per (Attempt + Question). Normalizes responses across question types and references uploaded files.

---

## Security

- OTP requests are rate-limited using Redis
- Attempt access protected by signed random tokens
- Strict ownership policies + scoped route model binding
- Sensitive file downloads are explicitly authorized
- Central exception handling with consistent, clean error responses

---

## Tech Stack

- **Laravel 13** + PHP 8.3
- **Sanctum** (for owner authentication)
- **MySQL 8** (primary database)
- **Redis** (rate limiting, OTP storage, queue)
- **Pest** (testing)
- **Pint** (code style)

---

## API Documentation

All API routes are under `/api/v1`.

**Full request/response examples** and up-to-date collections are available in the [`bruno/`](./bruno) directory. Import it into **Bruno** for the best developer experience.

### Authentication (Owner)

| Method | Endpoint                     | Auth   |
| ------ | ---------------------------- | ------ |
| POST   | `/auth/register/request-otp` | —      |
| POST   | `/auth/register/verify-otp`  | —      |
| POST   | `/auth/login`                | —      |
| GET    | `/auth/me`                   | Bearer |
| PATCH  | `/auth/me`                   | Bearer |
| POST   | `/auth/logout`               | Bearer |

### Exam Management (Owner)

| Method                     | Endpoint                                                     | Description              |
| -------------------------- | ------------------------------------------------------------ | ------------------------ |
| GET / POST                 | `/exams`                                                     | List / Create            |
| GET / PUT / PATCH / DELETE | `/exams/{exam}`                                              | Manage exam              |
| GET / POST                 | `/exams/{exam}/questions`                                    | Questions                |
| GET / PUT / PATCH / DELETE | `/exams/{exam}/questions/{question}`                         | Manage question          |
| GET                        | `/exams/{exam}/attempts`                                     | All attempts (paginated) |
| GET                        | `/exams/{exam}/attempts/{attempt}`                           | Attempt + answers        |
| GET                        | `/exams/{exam}/attempts/{attempt}/answers/{answer}/download` | Download file answer     |

### Public Endpoints (Anonymous Takers)

| Method | Endpoint                                                              | Auth              |
| ------ | --------------------------------------------------------------------- | ----------------- |
| GET    | `/public/exams/{slug}`                                                | —                 |
| POST   | `/public/exams/{slug}/attempts/request-otp`                           | —                 |
| POST   | `/public/exams/{slug}/attempts/verify-otp`                            | —                 |
| GET    | `/public/exams/{slug}/attempts/{attempt}/resume`                      | `X-Attempt-Token` |
| POST   | `/public/exams/{slug}/attempts/{attempt}/questions/{question}/answer` | `X-Attempt-Token` |
| POST   | `/public/exams/{slug}/attempts/{attempt}/complete`                    | `X-Attempt-Token` |

---

## Error Response Format

All errors follow a consistent structure:

```json
{
  "status": false,
  "message": "Validation failed",
  "errors": { ... }
}
```

Handled globally in `bootstrap/app.php`.

---

## Local Development

```bash
composer install
cp .env.example .env
php artisan key:generate

# Configure DB and REDIS in .env
php artisan migrate

# Development (server + queue + log tail)
composer run dev
```

Or manually:

```bash
php artisan serve
php artisan queue:listen
```

---

## Testing & Quality

- 65+ feature & unit tests covering auth, policies, public flows, file uploads, etc.
- CI pipeline runs **Pint** + full test suite on every push/PR

```bash
php artisan test --compact
vendor/bin/pint --dirty
```

---

## Roadmap

- [x] OTP-based anonymous attempts
- [x] Resume functionality
- [x] File upload support
- [x] Owner result management
- [ ] Automatic cleanup of abandoned attempts
- [ ] Question ordering management
- [ ] Attempt analytics

---

## License

MIT License
