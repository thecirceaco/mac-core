# MAC Core Alignment Todos

## Metadata

- [x] Require PHP `8.3` in plugin metadata and Composer configuration.
- [x] Require WordPress `6.9` in plugin metadata and `readme.txt`.
- [x] Mark tested up to WordPress `6.9`.
- [x] Keep license metadata on GPL v3 or later.
- [ ] Reconcile release versions across `mac-core.php`, `inc/constants.php`, `readme.txt`, and changelog entries.

## Composer And PHPCS

- [x] Add `composer.json` for the plugin.
- [x] Add `dealerdirect/phpcodesniffer-composer-installer` as a dev dependency.
- [x] Add `squizlabs/php_codesniffer` as a dev dependency.
- [x] Add `wp-coding-standards/wpcs` as a dev dependency.
- [x] Add `composer lint`.
- [x] Add `composer lint:fix`.
- [x] Add `phpcs.xml.dist` scoped to `mac-core.php`, `index.php`, `uninstall.php`, `inc`, and `src`.
- [x] Exclude `vendor/*` and `.codex/*` from PHPCS.

## PHPUnit

- [x] Add a WordPress plugin PHPUnit harness.
- [ ] Use the WP-CLI plugin test scaffold as the starting point where practical.
- [x] Adapt the harness for Composer-based dependencies.
- [x] Adapt bootstrap loading for MAC Core's OOP plugin bootstrap.
- [x] Add `composer test`.
- [x] Test plugin bootstrap behavior.
- [x] Test Kernel service registration behavior.
- [x] Test representative service or utility behavior without brittle WordPress admin UI assertions.

## CI

- [x] Add a PHP `8.3` Composer-backed GitHub Actions Coding Standards workflow based on `mac-bricks` and `mac-etch`.
- [x] Ensure CI runs `composer lint`.
- [x] Ensure CI runs `composer test`.

## Release Archives

- [x] Update `.github/workflows/release.yml` to exclude dev-only files from release ZIPs.
- [x] Update `.gitattributes` export ignores for dev-only files.
- [x] Ensure release archives exclude `AGENTS.md`, `.codex`, `.vscode`, `.ddev`, `composer.json`, `composer.lock`, `phpcs.xml.dist`, `phpunit.xml.dist`, `tests`, `bin/install-wp-tests.sh`, `vendor`, `node_modules`, `.env`, `.env.local`, `.github`, `README.md`, `readme.txt`, `dist`, `coverage`, `.phpunit.cache`, `.phpunit.result.cache`, and `.phpcs-cache`.
- [x] Keep the `mac-bricks` Composer and PHPCS release exclusions as the baseline, plus the MAC Core plugin-specific PHPUnit exclusions.

## Verification

- [x] Check `git status` before editing implementation files.
- [x] Preserve existing user edits, especially in `LICENSE`, `README.md`, `mac-core.php`, and `readme.txt`.
- [x] Run `composer install`.
- [x] Run `composer lint`.
- [x] Run `composer test`.
- [ ] Check release ZIP contents.
