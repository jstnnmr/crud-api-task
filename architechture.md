# Architecture & Code Design Guidelines
This document outlines the coding standards and architectural patterns used in this project. The goal is to keep the codebase consistent, maintainable, and easy to understand across all contributors.  

Follow these guidelines when writing or reviewing code.

## Core Principles

- Keep it simple (avoid premature abstraction)
- Be consistent across modules
- Prefer readability over cleverness
- Follow existing patterns before introducing new ones

## Architecture

We follow a **Controller → Service → Repository** pattern.

### Controller
- Handles request/response only
- No business logic
- No direct DB queries

### Service
- Contains business logic
- Orchestrates flow
- Can contain simple DB operations (if minimal and not reusable)

### Repository
- Handles DB operations
- Used when:
  - logic is reusable
  - queries are complex
  - multiple queries are involved

## Repository Usage

- Simple (1–2 queries, not reusable) → keep in Service
- Complex or reusable → move to Repository

Avoid over-abstraction.  
Prioritize clarity and consistency.

## API Guidelines

- Use appropriate HTTP status codes
- Follow standard conventions (e.g. **200, 201, 400, 401, 403, 404, 500**)
- See [HTTP status codes](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status) for full reference

## Service Layer Standard

- Prefer using `ServiceReturn` for standardized service responses when applicable.
- Use it to maintain consistency in response structure across services.
- Avoid returning raw arrays or mixed response formats when a standardized response is more suitable.

See implementation: [`app/Support/ServiceReturn.php`](/app/Support/ServiceReturn.php)

## Code Guidelines
- Use Eloquent relationships over manual queries when possible
- Avoid DB operations in controllers
- Check for existing [helper](/app/Helper/Helper.php) functions before creating new logic
- Add helper functions when logic is reusable and used across multiple parts of the system
- Use typed parameters, return types, and named arguments when applicable (Named arguments are available starting PHP 8+)

```php
// Service Layer
public function getUserById(int $id): ServiceReturn
{
    $user = $this->userRepository->findById(id: $id);

    return ServiceReturn::success(data: $user);
}
```

```php
// Repository Layer
public function findById(int $id): User
{
    return User::findOrFail($id);
}
```

## Semantic Commit Messages

Format:
`<type>(<scope>): <subject>`

### Examples

- `feat`: new feature for the user (not a build script change)
- `fix`: bug fix for the user (not a build script fix)
- `docs`: documentation changes
- `style`: formatting, missing semicolons, etc (no production code change)
- `refactor`: refactoring production code (e.g. renaming variables)
- `test`: adding or updating tests (no production code change)
- `chore`: maintenance tasks (e.g. updating configs, dependencies)

## PR Review Guidelines

Reviewers will check for:

- Use optimized queries and avoid redundant DB calls
- Controller, service, and repository responsibilities are respected
- Keep functions clean and aligned with SOLID principles (when applicable)
- Changes are consistent with existing code patterns
- Ensure functions are tested and cover relevant edge cases