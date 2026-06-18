# AGENTS.md

## Project

Laravel 12 REST API (EaseTask) — task management with auth, collaboration, notes, gamification.

## Commands

```bash
# Full setup (install + env + migrate + frontend build)
composer setup

# Development (runs server, queue worker, logs, vite concurrently)
composer dev

# Tests (clears config cache, uses SQLite in-memory)
composer test
# or: php artisan artisan test

# Seed database with test data
php artisan migrate:fresh --seed

# Build frontend assets
npm run build

# Dev server (frontend only)
npm run dev

# API docs
# Visit /api/documentation (L5-Swagger)
```

## Architecture

**Controller → Service → Repository** layered pattern.

- **Controllers**: HTTP request/response only. No business logic, no direct DB queries.
- **Services**: Business logic, orchestration. Use `ServiceReturn` DTO for responses.
- **Repositories**: DB operations. Only for complex/reusable queries.

`architechture.md` and `MODULE_GUIDE.md` are the authoritative sources.

## Key Gotchas

1. **Form Request classes are unused** — `StoreTaskRequest`, `UpdateTaskRequest`, etc. exist in `app/Http/Requests/` but are never injected. All validation is inline via `Validator::make()` in controllers. Do not add new Form Request classes unless refactoring.

2. **UsersController bypasses architecture** — reads go directly through `User::where()`, not the repository. Be aware if modifying.

3. **Test DB ≠ prod DB** — tests use SQLite in-memory (`phpunit.xml`), not MySQL. Test config is in `phpunit.xml` `<php>` section.

4. **`.env` must set `DB_CONNECTION=mysql`** — `config/database.php` defaults to sqlite if env is missing.

5. **Dual response pattern** — many controllers serve both JSON and Blade. Check `$request->wantsJson()` before adding new responses.

6. **Named arguments** — codebase uses PHP 8 named arguments extensively: `$repo->findById(id: $id)`.

7. **ServiceReturn DTO** — all services should return `ServiceReturn::success()` or `ServiceReturn::error()`. See `app/Support/ServiceReturn.php`.

8. **Sanctum auth** — API uses token auth (`auth:sanctum` middleware). Register/login via `POST /api/register` and `POST /api/login`.

## Testing

- PHPUnit with `tests/Feature` and `tests/Unit` suites
- Tests run against SQLite in-memory (fast, no MySQL needed)
- `composer test` clears config cache first
- Existing tests: `AiAssistantTest.php`, `ExampleTest.php`, `PaginationTest.php`
- Factories exist in `database/factories/`

## Database Seeding

```bash
php artisan migrate:fresh --seed
```

Test accounts (password: `password`):
- `test@example.com` (primary user)
- `collab1@example.com`, `collab2@example.com`, `collab3@example.com` (collaborators)

## AI Integration

Config in `config/ai.php`. Providers: `opencodego` (default) and `groq`. Keys in `.env` (`OPENAICODE_API_KEY`, `GROQ_API_KEY`).

## Conventions

- Semantic commits: `feat(scope): description`, `fix(scope): description`, etc.
- 4-space indent, UTF-8, LF line endings (`.editorconfig`)
- PHP 8.2+ required, Node 25+ (`.nvmrc`)
- Validation inline in controllers (not Form Requests)
- Eloquent relationships preferred over manual queries
- Check `app/Helper/Helper.php` before creating new utility logic
