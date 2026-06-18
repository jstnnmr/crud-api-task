# EaseTask — Full Module Guide

> A Laravel 12 task management application with authentication, collaboration, gamification, and notes.

---

## Table of Contents

1. [Tech Stack](#1-tech-stack)
2. [Architecture Pattern](#2-architecture-pattern)
3. [Directory Structure](#3-directory-structure)
4. [Database Schema](#4-database-schema)
5. [Entity Relationships](#5-entity-relationships)
6. [Module Breakdown](#6-module-breakdown)
   - [6.1 Authentication & Email Verification](#61-authentication--email-verification)
   - [6.2 User Management (Admin/Child Users)](#62-user-management-adminchild-users)
   - [6.3 Account Management](#63-account-management)
   - [6.4 Subjects](#64-subjects)
   - [6.5 Categories](#65-categories)
   - [6.6 Tasks](#66-tasks)
   - [6.7 Team Collaboration](#67-team-collaboration)
   - [6.8 Notes](#68-notes)
   - [6.9 Forgot / Reset Password](#69-forgot--reset-password)
7. [API Routes](#7-api-routes)
8. [Key Conventions](#8-key-conventions)
9. [Setup Instructions](#9-setup-instructions)
10. [Known Issues / Gotchas](#10-known-issues--gotchas)

---

## 1. Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 (`laravel/framework` ^12.0) |
| PHP | ^8.2 |
| Database | MySQL (via XAMPP / Herd) |
| Auth Tokens | Laravel Sanctum ^4.0 |
| API Docs | L5-Swagger (darkaonline/l5-swagger) + swagger-php |
| Frontend Assets | Vite (Laravel Vite plugin) |
| Mail | SMTP (Gmail) |
| Queue | Database-driven |

---

## 2. Architecture Pattern

The project follows a strict **Controller → Service → Repository** layered architecture.

```
HTTP Request
    │
    ▼
┌──────────────┐
│  Controller   │  ← Handles HTTP request/response only. No business logic.
│               │     No direct DB queries.
└──────┬───────┘
       │
       ▼
┌──────────────┐
│   Service     │  ← Business logic, orchestration, validation coordination.
│               │     May contain simple DB calls (1-2 queries, not reusable).
└──────┬───────┘
       │
       ▼
┌──────────────┐
│  Repository   │  ← DB operations. Used for complex/reusable queries.
│               │     Returns Eloquent models/collections.
└──────────────┘
```

**Standardized Response DTO** — `App\Support\ServiceReturn`

All services return `ServiceReturn` objects:
```php
ServiceReturn::success(data: $user, message: 'Done', status: 200);
ServiceReturn::error(message: 'Not found', status: 404);
```

Properties: `success` (bool), `data` (mixed), `message` (?string), `status` (int).

---

## 3. Directory Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php              (base controller)
│   │   ├── AuthController.php           (register, login, verify, logout)
│   │   ├── AccountController.php        (profile, password, photo)
│   │   ├── UsersController.php          (CRUD for child users)
│   │   ├── SubjectController.php        (CRUD for subjects)
│   │   ├── CategoryController.php       (CRUD for categories)
│   │   ├── TaskController.php           (CRUD, complete)
│   │   ├── NoteController.php           (CRUD, invite, collaborators)
│   │   ├── TeamController.php           (invite, invitations, activities)
│   │   ├── ForgotPasswordController.php (reset link + reset)
│   │   └── Api/
│   │       └── HomeController.php
│   ├── Requests/
│   │   ├── StoreTaskRequest.php         (NOT used — validation is inline)
│   │   ├── UpdateTaskRequest.php        (NOT used)
│   │   ├── StoreUserRequest.php         (NOT used)
│   │   └── UpdateUserRequest.php        (NOT used, authorize() returns false)
├── Models/
│   ├── User.php
│   ├── Task.php
│   ├── Subject.php
│   ├── Category.php
│   ├── Note.php
│   ├── TaskActivity.php
│   ├── TaskInvitation.php
│   └── Transaction.php
├── Services/
│   ├── AuthService.php
│   ├── AccountService.php
│   ├── UserService.php
│   ├── SubjectService.php
│   ├── CategoryService.php
│   ├── TaskService.php
│   ├── NoteService.php
│   ├── TeamService.php
│   └── ForgotPasswordService.php
├── Repositories/
│   ├── AuthRepository.php
│   ├── UserRepository.php
│   ├── SubjectRepository.php
│   ├── CategoryRepository.php
│   ├── TaskRepository.php
│   ├── NoteRepository.php
│   ├── TeamRepository.php
│   └── PasswordResetRepository.php
├── Mail/
│   ├── VerifyEmailMail.php
│   ├── ForgotPasswordMail.php
│   ├── PasswordChangeMail.php
│   ├── TaskInvitationMail.php
│   ├── NoteInvitationMail.php
│   └── OverdueTaskMail.php
└── Support/
    └── ServiceReturn.php
database/
└── migrations/     (20 migration files)
routes/
└── api.php         (all API route definitions)
```

---

## 4. Database Schema

### `users`
| Column | Type | Notes |
|---|---|---|
| id | bigint AI | PK |
| name | varchar(255) | |
| email | varchar(255) | UNIQUE |
| password | varchar(255) | hashed |
| role | varchar(255) | nullable |
| total_points | int | default 0 |
| photo | varchar(255) | nullable, storage path |
| verification_code | varchar(255) | nullable, 6-digit code |
| verification_code_expires_at | timestamp | nullable |
| email_verified_at | timestamp | nullable |
| created_by | bigint | nullable, FK to users (parent user) |
| remember_token | varchar(100) | nullable |
| created_at / updated_at | timestamps | |

### `subjects`
| Column | Type | Notes |
|---|---|---|
| id | bigint AI | PK |
| user_id | bigint | FK → users |
| name | varchar(255) | |
| color | varchar(7) | hex color, default `#8e7dff` |
| timestamps | | |

### `categories`
| Column | Type | Notes |
|---|---|---|
| id | bigint AI | PK |
| user_id | bigint | FK → users |
| name | varchar(255) | |
| timestamps | | |

### `tasks`
| Column | Type | Notes |
|---|---|---|
| id | bigint AI | PK |
| subject_id | bigint | FK → subjects (ON DELETE CASCADE) |
| category_id | bigint | FK → categories (nullable, ON DELETE SET NULL) |
| title | varchar(255) | |
| description | text | nullable |
| priority | enum('low','medium','high') | default 'medium' |
| status | enum('pending','in_progress','completed') | default 'pending' |
| points_earned | int | default 0 |
| due_date | date | nullable |
| completed_at | timestamp | nullable |
| timestamps | | |

### `task_user` (pivot)
| Column | Type | Notes |
|---|---|---|
| task_id | bigint | FK → tasks |
| user_id | bigint | FK → users |
| role | varchar(255) | default 'collaborator' |
| timestamps | | |
| UNIQUE(task_id, user_id) | | |

### `task_activities`
| Column | Type | Notes |
|---|---|---|
| id | bigint AI | PK |
| task_id | bigint | FK → tasks |
| user_id | bigint | FK → users |
| action | varchar(255) | e.g. 'created', 'updated', 'completed', 'invited', 'joined' |
| changes | json | nullable, diff of updated fields |
| timestamps | | |

### `task_invitations`
| Column | Type | Notes |
|---|---|---|
| id | bigint AI | PK |
| task_id | bigint | FK → tasks |
| invited_by | bigint | FK → users |
| invited_email | varchar(255) | |
| token | varchar(255) | UNIQUE, random 40-char |
| status | varchar(255) | default 'pending' (pending/accepted/declined) |
| timestamps | | |

### `notes`
| Column | Type | Notes |
|---|---|---|
| id | bigint AI | PK |
| user_id | bigint | FK → users (owner) |
| title | varchar(255) | |
| content | longtext | nullable |
| color | varchar(7) | hex, default `#fff9c4` |
| timestamps | | |

### `note_user` (pivot)
| Column | Type | Notes |
|---|---|---|
| note_id | bigint | FK → notes |
| user_id | bigint | FK → users |
| role | varchar(255) | default 'collaborator' |
| timestamps | | |
| UNIQUE(note_id, user_id) | | |

### `transactions`
| Column | Type | Notes |
|---|---|---|
| id | bigint AI | PK |
| user_id | bigint | FK → users |
| amount | decimal | |
| description | text | |
| type | varchar(255) | |
| timestamps | | |

Additional system tables: `personal_access_tokens` (Sanctum), `cache`, `jobs`, `sessions`, `password_reset_tokens`.

---

## 5. Entity Relationships

```
User (1) ──hasMany──► Subject (1) ──hasMany──► Task
  │                     │
  │                     └── hasManyThrough ──► Task (via Subject)
  │
  ├── hasMany ──► Category
  ├── hasMany ──► Note
  ├── hasMany ──► Transaction
  ├── hasMany ──► TaskActivity
  │
  ├── belongsToMany ──► Task (collaborating via task_user)
  ├── belongsToMany ──► Note (collaborating via note_user)
  │
  └── hasMany ──► TaskInvitation (as inviter)

Task (1) ──belongsTo──► Subject
Task (1) ──belongsTo──► Category (nullable)
Task (1) ──belongsToMany──► User (collaborators via task_user)
Task (1) ──hasMany──► TaskActivity
Task (1) ──hasMany──► TaskInvitation

Note (1) ──belongsTo──► User (owner)
Note (1) ──belongsToMany──► User (collaborators via note_user)
```

---

## 6. Module Breakdown

### 6.1 Authentication & Email Verification

**Controllers:** `AuthController.php`
**Service:** `AuthService.php`
**Repository:** `AuthRepository.php`
**Mail:** `VerifyEmailMail.php`

**Endpoints:**
- `POST /api/register` — Create account (name, email, password with confirmation). Sends 6-digit verification code via email.
- `POST /api/login` — Email + password. Returns user + Sanctum plain text token.
- `POST /api/verify-email` — Verify with email + 6-digit code. Code expires after 10 minutes.
- `POST /api/verify-email/resend` — Resend verification code.

**Flow:**
1. User registers → user created with `password` (hashed), `verification_code` set, `email_verified_at` null.
2. Verification email sent with 6-digit code.
3. User verifies via `POST /api/verify-email` → `email_verified_at` set, code cleared.
4. Web session users are auto-logged in via `Auth::login()`.

**Validation rules** (register):
- name: required, string, max:255
- email: required, unique:users
- password: required, min:8, must contain 1 number, confirmed

**`apiLogin`** (separate method): Returns `{ success, data: { user, token }, message }`. Uses `Hash::check()` directly — simpler, no `Auth::login()`.

---

### 6.2 User Management (Admin/Child Users)

**Controller:** `UsersController.php`
**Service:** `UserService.php`
**Repository:** `UserRepository.php`

> **Note:** This is NOT standard auth-user CRUD. It manages "child" users scoped to the authenticated user via the `created_by` column. Think of it as "people I manage."

**Endpoints (auth required):**
- `GET /api/users` — List all users where `created_by = auth()->id()`, with tasks eager-loaded.
- `GET /api/users/{id}` — Show single user (scoped to `created_by`).
- `POST /api/users` — Create child user (name, email). Sets `created_by = auth()->id()`.
- `PUT /api/users/{id}` — Update child user (scoped to `created_by`).
- `DELETE /api/users/{id}` — Delete child user (scoped to `created_by`).

**Key detail:** Both JSON and Blade view responses are supported. The controller checks `$request->wantsJson()` or `$request->is('api/*')` to decide.

---

### 6.3 Account Management

**Controller:** `AccountController.php`
**Service:** `AccountService.php`
**Repository:** `UserRepository.php`
**Mail:** `PasswordChangeMail.php`

**Endpoints (auth required):**
- `GET /api/account` — Show current user profile (name, email, photo URL, role).
- `PUT /api/account` — Update name/email. Checks for duplicate email.
- `PUT /api/account/password` — Initiate password change. Verifies current password, sends 6-digit code to email.
- `POST /api/account/password/confirm` — Confirm password change with code + new password.
- `POST /api/account/photo` — Upload profile photo (image, max 2MB, formats: jpg/jpeg/png/webp/gif). Stored in `storage/app/public/photos/`. Old photo deleted on replace.

**Password change flow:**
1. User provides `current_password` + `new_password` + `new_password_confirmation`.
2. Service verifies current password → sends code via email.
3. User provides `code` + `new_password` + `new_password_confirmation` to confirm.

---

### 6.4 Subjects

**Controller:** `SubjectController.php`
**Service:** `SubjectService.php`
**Repository:** `SubjectRepository.php`

Subjects are the top-level organizational unit for tasks (e.g., "Math", "Work Projects", "Fitness"). Each subject belongs to a user.

**Endpoints (auth required, `apiResource`):**
- `GET /api/subjects` — List subjects with task counts (`tasks_count`, `completed_tasks_count`).
- `POST /api/subjects` — Create (name required, color optional, default `#8e7dff`).
- `GET /api/subjects/{id}` — Show subject with tasks and categories eager-loaded.
- `PUT /api/subjects/{id}` — Update name/color.
- `DELETE /api/subjects/{id}` — Delete subject (cascades to tasks).

**Subject model** has a computed attribute `getProgressAttribute()`: percentage of completed tasks in the subject (0-100).

Blade view endpoints:
- `GET /subjects/data` — Renders `users.data` view with subjects + categories.

---

### 6.5 Categories

**Controller:** `CategoryController.php`
**Service:** `CategoryService.php`
**Repository:** `CategoryRepository.php`

Categories are optional labels for tasks (e.g., "Urgent", "Backlog"). Scoped to user.

**Endpoints (auth required, `apiResource`):**
- `GET /api/categories` — List categories.
- `POST /api/categories` — Create (name required).
- `PUT /api/categories/{id}` — Update name.
- `DELETE /api/categories/{id}` — Delete category (tasks' `category_id` set to null).

**Resolution via `CategoryService::firstOrCreate()`** — When creating/updating a task, if `category_name` is provided instead of `category_id`, the system auto-creates or reuses a category with that name.

---

### 6.6 Tasks

**Controller:** `TaskController.php`
**Service:** `TaskService.php`
**Repository:** `TaskRepository.php`

Tasks are the core work item. They belong to a Subject and optionally to a Category.

**Endpoints (auth required, `apiResource` + extra):**
- `GET /api/tasks` — List tasks accessible by the user (owned via subject, or collaborating).
- `POST /api/tasks` — Create task.
  - Fields: `subject_id` (required, must belong to user), `category_id`/`category_name`, `title`, `description`, `priority` (low/medium/high), `status` (pending/in_progress/completed), `due_date`.
- `GET /api/tasks/{id}` — Show task (user must own or collaborate).
- `PUT /api/tasks/{id}` — Update task fields.
- `DELETE /api/tasks/{id}` — Delete task (owner only via `findOwnedById`).
- `PATCH /api/tasks/{id}/complete` — Mark task completed, award points, create activity log.

**Task completion & points:**

| Priority | Points |
|---|---|
| low | 5 |
| medium | 10 |
| high | 20 |

- Completing a task (via `complete` route or updating status to `completed`):
  - Awards points to `User.total_points`
  - Sets `points_earned` on the task
  - Sets `completed_at` timestamp
  - Creates `TaskActivity` with action `"completed"` (or `"updated"` with changes diff)
- Deleting a completed task **deducts** its points from the user.

**Category resolution:**
When creating/updating a task, the service resolves the category:
1. If `category_id` provided → use it directly.
2. If `category_name` provided → `firstOrCreate` with `user_id` and name.
3. Otherwise → null.

**Activity logging:**
- Creating a task logs `"created"` action.
- Updating logs `"updated"` with a `changes` JSON diff of modified fields.
- Completing via patch logs `"completed"`.
- No activity for deletion.

**Task query scoping (in Repository):**
- `getAllByUser()`: Tasks where the subject's `user_id` matches.
- `findByIdAndUser()`: Tasks where user is owner (via subject) OR is a collaborator (via `task_user`).
- `findOwnedById()`: Tasks where user is the owner ONLY (for delete operations).

---

### 6.7 Team Collaboration

**Controller:** `TeamController.php`
**Service:** `TeamService.php`
**Repository:** `TeamRepository.php`
**Mail:** `TaskInvitationMail.php`

Collaboration allows multiple users to work on the same task.

**Endpoints (auth required):**
- `GET /api/my-tasks` — All tasks visible to user (owned + collaborating).
- `GET /api/team/invitations` — Pending invitations for the authenticated user (matched by email).
- `POST /api/team/invite` — Invite user to a task by email. Creates `TaskInvitation` with random 40-char token. Sends email notification.
  - Validates: user must own the task, not already a collaborator, no pending invitation for same email.
- `POST /api/team/invitations/{token}/accept` — Accept invitation. Adds user as collaborator (`role: 'collaborator'`), logs `"joined"` activity.
  - Validates: invitation must be pending, token must match user's email.
- `POST /api/team/invitations/{token}/decline` — Decline invitation. Sets status to `'declined'`.
- `GET /api/team/tasks/{taskId}/collaborators` — List collaborators for a task (owner only).
- `DELETE /api/team/tasks/{taskId}/collaborators/{collaboratorId}` — Remove collaborator (owner only). Logs `"removed_collaborator"` activity.
- `GET /api/team/tasks/{taskId}/activities` — Activity log for a task (owner or collaborator).

**Key behaviors:**
- Invitations are email-based. The invited user must already have an account with that email.
- Invitation statuses: `pending` → `accepted` | `declined`.
- Duplicate pending invitations for the same email/task are prevented.
- Emails are "best-effort" — failures caught silently; the DB record is still saved.

---

### 6.8 Notes

**Controller:** `NoteController.php`
**Service:** `NoteService.php`
**Repository:** `NoteRepository.php`
**Mail:** `NoteInvitationMail.php`

Simple notes with collaboration.

**Endpoints (auth required):**
- `GET /api/notes` — List notes (owned + collaborating).
- `POST /api/notes` — Create note (title optional, defaults to "Untitled"; content optional; color optional, defaults to `#fff9c4`).
- `GET /api/notes/{id}` — Show note (owner or collaborator).
- `PUT /api/notes/{id}` — Update title/content/color.
- `DELETE /api/notes/{id}` — Delete note (owner only).
- `POST /api/notes/{id}/invite` — Add collaborator by email. User must exist.
  - Validates: owner only, not already a collaborator, user exists by email.
- `DELETE /api/notes/{id}/collaborators/{collaboratorId}` — Remove collaborator (owner only).

**Note model** has a computed `preview` attribute: strips HTML tags, limits to 120 chars; returns `"Empty note"` if no content.

---

### 6.9 Forgot / Reset Password

**Controller:** `ForgotPasswordController.php`
**Service:** `ForgotPasswordService.php`
**Repository:** `PasswordResetRepository.php`
**Mail:** `ForgotPasswordMail.php`

**Endpoints (no auth required):**
- `POST /api/forgot-password` — Send reset link to email.
  - Creates 60-char token in `password_reset_tokens` table.
  - Token expires after 60 minutes.
  - Sends email with reset URL.
- `POST /api/reset-password` — Reset password with token.
  - Validates: email + token + password (min 8, 1 number) + confirmation.
  - On success: updates password, auto-verifies email (if not already), deletes token.

---

## 7. API Routes

All routes are defined in `routes/api.php`. Prefix: `/api`.

### Public Routes (no auth)

| Method | URI | Controller Method |
|---|---|---|
| POST | `/api/register` | AuthController@register |
| POST | `/api/login` | AuthController@apiLogin |
| POST | `/api/verify-email` | AuthController@verify |
| POST | `/api/verify-email/resend` | AuthController@resendCode |
| POST | `/api/forgot-password` | ForgotPasswordController@sendResetLink |
| POST | `/api/reset-password` | ForgotPasswordController@reset |

### Authenticated Routes (auth:sanctum)

| Method | URI | Controller Method |
|---|---|---|
| GET | `/api/account` | AccountController@show |
| PUT | `/api/account` | AccountController@updateProfile |
| PUT | `/api/account/password` | AccountController@updatePassword |
| POST | `/api/account/password/confirm` | AccountController@confirmPasswordChange |
| POST | `/api/account/photo` | AccountController@updatePhoto |
| GET | `/api/users` | UsersController@index |
| POST | `/api/users` | UsersController@store |
| GET | `/api/users/{id}` | UsersController@show |
| PUT | `/api/users/{id}` | UsersController@update |
| DELETE | `/api/users/{id}` | UsersController@destroy |
| GET | `/api/subjects` | SubjectController@index |
| POST | `/api/subjects` | SubjectController@store |
| GET | `/api/subjects/{id}` | SubjectController@show |
| PUT | `/api/subjects/{id}` | SubjectController@update |
| DELETE | `/api/subjects/{id}` | SubjectController@destroy |
| GET | `/api/categories` | CategoryController@index |
| POST | `/api/categories` | CategoryController@store |
| PUT | `/api/categories/{id}` | CategoryController@update |
| DELETE | `/api/categories/{id}` | CategoryController@destroy |
| GET | `/api/tasks` | TaskController@index |
| POST | `/api/tasks` | TaskController@store |
| GET | `/api/tasks/{id}` | TaskController@show |
| PUT | `/api/tasks/{id}` | TaskController@update |
| DELETE | `/api/tasks/{id}` | TaskController@destroy |
| PATCH | `/api/tasks/{id}/complete` | TaskController@complete |
| GET | `/api/notes` | NoteController@list |
| POST | `/api/notes` | NoteController@store |
| GET | `/api/notes/{id}` | NoteController@show |
| PUT | `/api/notes/{id}` | NoteController@update |
| DELETE | `/api/notes/{id}` | NoteController@destroy |
| POST | `/api/notes/{id}/invite` | NoteController@invite |
| DELETE | `/api/notes/{id}/collaborators/{cid}` | NoteController@removeCollaborator |
| GET | `/api/my-tasks` | TeamController@getMyTasks |
| GET | `/api/team/invitations` | TeamController@getInvitations |
| POST | `/api/team/invite` | TeamController@invite |
| POST | `/api/team/invitations/{token}/accept` | TeamController@acceptInvitation |
| POST | `/api/team/invitations/{token}/decline` | TeamController@declineInvitation |
| GET | `/api/team/tasks/{taskId}/collaborators` | TeamController@getCollaborators |
| DELETE | `/api/team/tasks/{taskId}/collaborators/{cid}` | TeamController@removeCollaborator |
| GET | `/api/team/tasks/{taskId}/activities` | TeamController@getActivities |
| GET | `/api/me/stats` | Inline closure |

---

## 8. Key Conventions

### Response Format (JSON)

**Success:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Action completed"
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error description"
}
```

**Validation errors:**
```json
{
  "errors": { "field": ["Validation message"] }
}
```

### HTTP Status Codes

| Code | Usage |
|---|---|
| 200 | Success (GET, PUT, PATCH, DELETE) |
| 201 | Created (POST) |
| 400 | Bad request / validation |
| 401 | Invalid credentials |
| 403 | Unauthorized (not owner) |
| 404 | Resource not found |
| 422 | Validation errors / conflict |
| 500 | Server error |

### Validation Style

Validation is done **inline** in controllers using `Validator::make()`, NOT via Form Request classes. The Form Request files (`StoreTaskRequest.php`, etc.) exist but are **unused**.

### Dual Response (JSON + Blade)

Many controllers serve both API JSON responses and Blade view responses. Pattern:
```php
if ($request->wantsJson()) {
    return response()->json([...]);
}
return view('...', compact(...));
```

### Named Arguments (PHP 8+)

The codebase uses PHP 8 named arguments extensively, e.g.:
```php
$this->taskRepository->findByIdAndUser(id: $id, userId: $userId);
```

---

## 9. Setup Instructions

1. **Clone the repository** and run `composer install`.
2. **Copy environment:** `cp .env.example .env` (or copy `.env.example` to `.env`).
3. **Generate app key:** `php artisan key:generate`.
4. **Configure `.env`:**
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=crud-api
   DB_USERNAME=root
   DB_PASSWORD=
   ```
5. **Create MySQL database** named `crud-api`.
6. **Run migrations:** `php artisan migrate`.
7. **Create storage link:** `php artisan storage:link`.
8. **Install frontend:** `npm install && npm run build` (or `npm run dev`).
9. **Start dev server:** `php artisan serve`.
10. **(Optional) Run queue worker** for mail: `php artisan queue:listen --tries=1 --timeout=0`.
11. **(Optional) View API docs:** `/api/documentation` (L5-Swagger).

---

## 10. Known Issues / Gotchas

1. **Form Request classes are unused** — `StoreTaskRequest`, `UpdateTaskRequest`, `StoreUserRequest`, `UpdateUserRequest` exist in `app/Http/Requests/` but are never injected into controllers. Validation is all inline. `UpdateUserRequest::authorize()` returns `false` (would deny if used).

2. **UsersController bypasses Service/Repository** — It queries `User::where(...)` directly and only uses `UserService` for create/update/delete operations but not for read operations (index, show). This violates the architecture guide.

3. **Dual-role `AuthController`** — Handles both web session auth (`login`, `logout`) and API token auth (`apiLogin`). The web methods use `Auth::login()` while `apiLogin` manually creates tokens.

4. **`config/database.php` line 20** — Default connection is `sqlite` (falls back from `env('DB_CONNECTION', 'sqlite')`). Make sure `.env` has `DB_CONNECTION=mysql`.

5. **MySQL error 1932** — If you get "Table doesn't exist in engine", your DB config lines in `.env` are likely commented out, causing fallback to a broken `laravel` database. Uncomment the DB settings.

6. **Points deduction on delete** — If you delete a task that was marked completed, the system deducts points. This could lead to negative `total_points`.

7. **Email is best-effort** — In invitation flows (task/note), email sending failures are silently caught. The DB record is still created.

8. **No pagination** — List endpoints (tasks, notes, subjects) return all records without pagination. Could be an issue at scale.

9. **OverdueTaskMail** exists in `app/Mail/` but no scheduler or command registers it. It's likely intended for a future cron job.
