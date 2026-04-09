# MAC Core Alignment Plan

## Goal

Align MAC Core with the current `mac-bricks` and `mac-etch` baseline while preserving MAC Core's OOP plugin architecture.

## Architecture Constraints

- Keep plugin code under the `MacCore` namespace.
- Keep `mac-core.php` as a minimal bootstrap with plugin metadata, `declare(strict_types=1)`, the `ABSPATH` guard, includes for `inc/constants.php` and `inc/autoload.php`, and `\MacCore\Kernel::boot()`.
- Keep `src/Kernel.php` responsible for service loading.
- Keep `MacCore\Contracts\Service` as the service contract for hook-registering services.
- Add WordPress behavior as service classes inside the module folders under `src/`; each service should implement `MacCore\Contracts\Service`, register hooks inside `register()`, and be added to the Kernel service list.
- Add shared pure helpers under `src/Utils/...` when they do not need to register hooks.
- Do not port the procedural theme organization from `mac-bricks` or `mac-etch` into MAC Core.

## Platform And Release Metadata

- Update platform metadata to require PHP `8.3`.
- Update WordPress metadata to require WordPress `6.9` and mark tested up to WordPress `6.9`.
- Keep the project on GPL v3 or later.
- Reconcile version metadata across `mac-core.php`, `MAC_CORE_VERSION` in `inc/constants.php`, `readme.txt` stable tag, and changelog entries before release.

## Composer And PHPCS Tooling

- Add Composer dev tooling that mirrors the theme repos:
  - `dealerdirect/phpcodesniffer-composer-installer`
  - `squizlabs/php_codesniffer`
  - `wp-coding-standards/wpcs`
- Add Composer scripts:
  - `composer lint` runs `phpcs --standard=phpcs.xml.dist`
  - `composer lint:fix` runs `phpcbf --standard=phpcs.xml.dist`
- Add `phpcs.xml.dist` scoped to:
  - `mac-core.php`
  - `index.php`
  - `uninstall.php`
  - `inc`
  - `src`
- Exclude root `vendor/*`, `inc/Vendor/*`, and `.codex/*` from PHPCS.

## CI And Release Archives

- Add the same PHP `8.3` Composer-backed GitHub Actions Coding Standards workflow used by `mac-bricks` and `mac-etch`.
- Include CI coverage for both `composer lint` and `composer test`.
- Update the release ZIP workflow so dev-only files stay out of release archives.
- Update `.gitattributes` export ignores for dev-only files, including:
  - `AGENTS.md`
  - `.codex`
  - `.vscode`
  - `.ddev`
  - `composer.json`
  - `composer.lock`
  - `phpcs.xml.dist`
  - `phpunit.xml.dist`
  - `tests`
  - `bin/install-wp-tests.sh`
  - `vendor`
  - `node_modules`
  - `.env`
  - `.env.local`
  - `.github`
  - `README.md`
  - `readme.txt`
  - `dist`
  - `coverage`
  - `.phpunit.cache`
  - `.phpunit.result.cache`
  - `.phpcs-cache`
- The `mac-bricks` release workflow excludes Composer and PHPCS dev tooling. MAC Core should keep that baseline and additionally exclude PHPUnit harness artifacts, test caches, coverage output, and local editor/dev-environment folders because this plugin will add plugin tests.

## PHPUnit Harness

- Add a WordPress plugin PHPUnit harness for MAC Core even though no PHPUnit suite was found in `mac-bricks` or `mac-etch` during planning.
- Prefer the WP-CLI plugin test scaffold as the starting point: https://developer.wordpress.org/cli/commands/scaffold/plugin-tests/
- Adapt the scaffold for Composer-based dependencies and the MAC Core OOP bootstrap.
- Use WP PHPUnit documentation for the Composer-compatible test library setup: https://github.com/wp-phpunit/docs
- Add `composer test`.
- Cover at minimum:
  - Plugin bootstrap behavior.
  - Kernel service registration behavior.
  - Representative service or utility behavior that can be tested without brittle WordPress admin UI assertions.

## Verification

- Before editing implementation files, check `git status` and preserve user changes.
- If `LICENSE`, `README.md`, `mac-core.php`, or `readme.txt` already contain uncommitted edits, build on them instead of replacing them.
- For the future implementation pass, verify with:
  - `composer install`
  - `composer lint`
  - `composer test`
  - A release ZIP contents check.
