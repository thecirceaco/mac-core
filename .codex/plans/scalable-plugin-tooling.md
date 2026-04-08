# Scalable Plugin Tooling And Architecture Plan

## Goal

Keep MAC Core small now, but set up professional plugin tooling so future features can grow without turning into procedural glue.

## Composer

- Add root `composer.json`.
- Use Composer first for development tooling:
  - PHPCS/WPCS.
  - PHPUnit.
  - Composer scripts.
- Do not require runtime `vendor/` in the release ZIP until MAC Core has runtime Composer dependencies.
- If runtime dependencies are added later, update release workflow to install production dependencies and include optimized `vendor/`.

## Static Analysis And Coding Standards

- Add `phpcs.xml.dist`.
- Run PHPCS through `composer lint`.
- Run PHPCBF through `composer lint:fix`.
- Scope linting to plugin-owned runtime code:
  - `mac-core.php`
  - `index.php`
  - `uninstall.php`
  - `inc`
  - `src`
- Exclude:
  - `.codex`
  - `.github`
  - `vendor`
  - `licensing`
  - `tests`
  - generated caches/output.

`licensing` is vendored third-party SDK code; do not lint it as first-party code unless applying a deliberate vendor patch.

## PHPUnit

- Add a root test harness under `tests/`.
- Prefer tests that can run quickly without a full WordPress database when possible.
- Keep the harness compatible with WordPress test suites so integration tests can be added later.
- Start with security-focused tests:
  - plain helper output is escaped,
  - utility functions handle invalid input safely,
  - service hook registration can be tested through stubs.

## CI

- Add a GitHub Actions workflow for:
  - PHP 8.3,
  - `composer install`,
  - `composer lint`,
  - `composer test`.
- Keep release workflow separate from quality checks.

## Future Module Boundaries

Use these only when a real feature needs them:

- `src/Admin` for custom admin UI/forms.
- `src/Licensing` if licensing grows beyond the current service wrapper.
- `src/Rest` for REST endpoints and route registration.
- `src/Assets` for admin/frontend asset registration.
- `src/Lifecycle` and `src/Migrations` before storing structured versioned data.
- `src/Config` for typed internal configuration.

Avoid empty folders and abstract base classes that do not yet remove real complexity.

## Release Discipline

- Work on `dev`.
- Use `main` only for the release/tag workflow.
- Keep dev tooling, `.codex`, test harness, and generated caches out of the release ZIP.
- Include runtime code, release metadata, and runtime licensing SDK files.
