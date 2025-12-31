# AI Agent Guide

You are Junie (JetBrains AI coding agent). Follow these rules for EVERY task.

## Operating mode and workflow
- Code in english.
- Start in **Ask mode** if the task is ambiguous or affects many files; summarize your understanding and propose a plan before edits.
- Use **Code mode** only after a clear plan exists and you know what files/commands you'll touch.
  (Junie supports Ask (read-only) and Code (edit/run) modes.)

- Always follow this loop:
  1) Restate goal in 1?2 lines.
  2) List exact files you will change/create.
  3) Implement minimal diff.
  4) Run checks/tests - execute `make qa` to run all checks and tests.
     Fix any issues found by the `make qa` checks.
     Repeat "`make qa` and fix" loop until all checks pass, but not more than five times.
  5) Summarize what changed + how to verify manually.


## Safety / permissions / risky actions
- NEVER use "brave mode". Prefer explicit approvals or a minimal allowlist.
- Do NOT run destructive commands (rm, del, format, disk tools), or anything that modifies OS/user files.
- Do NOT read/write outside the project directory unless explicitly instructed.
- Do NOT change build scripts / dependency definitions unless explicitly requested:
  composer.json, composer.lock, package.json, pnpm-lock.yaml, yarn.lock, Dockerfile, CI configs.
- Do NOT install new system packages, services, or daemons.

## Project stack assumptions
- PHP 8.4, Symfony 8, Doctrine ORM 3, Symfony UX (Stimulus/Turbo/Live Components where applicable).
- Keep the codebase "Symfony standard": services + DI, thin controllers, domain/business logic in services.

## Architecture rules (Symfony)
### Key Directories
- `app/src/` - Application source code (Symfony structure)
- `app/src/Domain` - Domain layer
- `app/src/Application` - Application layer
- `app/src/Infrastructure` - Infrastructure layer
- `app/src/Ux` - Ux layer
- `app/tests/` - Test suites (functional, integration, unit, api)
- `app/config/` - Configuration files
- `docker/` - Docker configuration and scripts
- `tools/` - Development tools (separate composer.json)

### Controllers
- Controllers only orchestrate:
  - parse request / route params
  - validate input (Form/Validator)
  - call an Application/Domain service
  - return Response (HTML/JSON/redirect)
- Controllers must NOT:
  - contain business rules
  - build complex Doctrine queries
  - flush EntityManager directly unless the project already does it that way and it's consistent

### Services
- Prefer constructor injection.
- Keep services stateless.
- Prefer small, composable services over a "God service".

### Templates (Twig)
- No business logic in Twig.
- Keep templates display-only (loops/conditions for rendering are fine).

## Doctrine ORM 3 rules
- Doctrine resides in the Infrastructure layer. No Domain/Application layer should know anything about Doctrine.
- Entities:
  - typed properties
  - invariants enforced via methods (no "setEverything")
  - keep entity methods small and meaningful
- Relationships:
  - prefer unidirectional relations unless bidirectional navigation is required
  - avoid deep cascade chains unless explicitly needed
- Repositories:
  - query logic lives in repositories (or dedicated query services)
  - avoid N+1 queries (use joins/selects intentionally)
- Migrations:
  - every schema change must include a migration
  - do not "fix" old migrations without explicit instruction

## Symfony UX rules
- Prefer "HTML-first" with progressive enhancement.
- Use Stimulus controllers for UI behavior only (no business logic in JS).
- If using Turbo/Frames/Streams or Live Components, keep state/validation server-side.

## Coding standards (PHP)
- declare(strict_types=1);
- Prefer readonly DTOs for request/response data objects.
- Use PHP attributes as used in the project (routing/ORM/validation).
- No new magic, no clever metaprogramming.

## Testing & quality gates (always run when code changes)
All tests should follow GIVEN/WHEN/THEN pattern
  Use `// GIVEN`, `// WHEN`, `// THEN` comments as separators in tests
Use descriptive test method names starting with test or functional description
Run the most relevant subset (fast first):
- make qa (checks all test, phpstan and cs-fixer)
- make tt (run all tests), make t{u,f,a,i} t=TestName (run specific test suite)
- make phpstan (phpstan level max)
- make cf (coding standards fixer)
- make sc c="lint:twig" (if Twig/templates changed)
- make sc c="lint:container" (if services/config changed)

If a command fails, stop and fix before continuing.

## Change scope rules
- Prefer minimal diffs.
- Do not refactor unrelated code.
- Do not rename public APIs without explicit request.
- If the task impacts multiple layers, implement in this order:
  Domain/Service -> Persistence/Repo -> Controller -> Templates/UX -> Tests

## Deliverables format
When you finish:
- Provide a short summary
- List changed files
- Provide exact commands to run locally
- Mention any manual QA steps (routes/pages to click)

## Environment
- Runtime is containerized. Do not use host PHP.
- Composer and PHP requirements are pinned for PHP version configured in `docker/dev.Dockerfile`.
- Run commands and tests using `make` (preferred), `docker compose` or `docker exec`.
- Application code is in `app/` directory.


## Commands

### Setup & Cleanup
```bash
make setup           # Full project setup (configure, rebuild, install, load-mocks)
make cleanup         # Clean containers, volumes, cache, vendor
make cln             # Shortcut for cleanup
make up              # Start containers
make down            # Stop containers
```

### Testing
```bash
make tt              # Run all tests
make tc              # Tests with coverage  
make tu t=TestName   # Unit tests
make ta t=TestName   # API tests
make ti t=TestName   # Integration tests
make tf t=TestName   # Functional tests
make tff             # Tests with fail-fast
make lm              # Load mocks
make l c=php         # View container logs
```

### Code Quality
```bash
make qa              # Pre-commit checks (cs-fixer, tests, phpstan)
make cf              # Fix code style
make phpstan         # Static analysis
```

### Database
```bash
make migrations-diff     # Generate migration
make migrations-migrate  # Run migrations
make migrations-down     # Rollback migration
make lf                  # Load fixtures
```

### Development Tools
```bash
make ia              # Interactive shell (ash)
make sc c="command"  # Run Symfony console command
make cp c="command"  # Run composer command
make doc             # Create OpenAPI schema
```

### Additional Shortcuts
```bash
make cln             # cleanup
make dc c="command"  # docker compose command
make ug              # upgrade (cleanup + setup)
```

