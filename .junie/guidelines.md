# Tasks API - AI Agent Guide

## Environment

- Runtime is containerized. Do not use host PHP.
- Composer and PHP requirements are pinned for PHP version configured in `docker/dev.Dockerfile`.
- Run commands and tests using `make` (preferred), `docker compose` or `docker exec`.
- Application code is in `app/` directory.

## Development

- Use PHPUnit.
- After finishing a task, execute `make quality-all` to run all checks and tests.
  Fix any issues found by the `make quality-all` checks. 
  Repeat "`make quality-all` and fix" loop until all checks pass, but not more than five times.

## Project

### Key Directories
- `app/src/` - Application source code (Symfony structure)
- `app/tests/` - Test suites (functional, integration, unit, api)
- `app/config/` - Configuration files
- `docker/` - Docker configuration and scripts
- `tools/` - Development tools (separate composer.json)

## Tests

### Test Structure
- Tests use Codeception with `*Test.php` suffix
- All tests should follow GIVEN/WHEN/THEN pattern
  Use `// GIVEN`, `// WHEN`, `// THEN` comments as separators in tests
- Use descriptive test method names starting with test or functional description

### Common Patterns


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

