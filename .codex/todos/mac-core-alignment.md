# MAC Core Alignment Todos

## Metadata

- [ ] Require PHP `8.3` in plugin metadata and Composer configuration.
- [ ] Require WordPress `6.9` in plugin metadata and `readme.txt`.
- [ ] Mark tested up to WordPress `6.9`.
- [ ] Keep license metadata on GPL v3 or later.
- [ ] Reconcile release versions across `mac-core.php`, `inc/constants.php`, `readme.txt`, and changelog entries.

## Composer And PHPCS

- [ ] Add `composer.json` for the plugin.
- [ ] Add `dealerdirect/phpcodesniffer-composer-installer` as a dev dependency.
- [ ] Add `squizlabs/php_codesniffer` as a dev dependency.
- [ ] Add `wp-coding-standards/wpcs` as a dev dependency.
- [ ] Add `composer lint`.
- [ ] Add `composer lint:fix`.
- [ ] Add `phpcs.xml.dist` scoped to `mac-core.php`, `index.php`, `uninstall.php`, `inc`, and `src`.
- [ ] Exclude `vendor/*` and `.codex/*` from PHPCS.

## PHPUnit

- [ ] Add a WordPress plugin PHPUnit harness.
- [ ] Use the WP-CLI plugin test scaffold as the starting point where practical.
- [ ] Adapt the harness for Composer-based dependencies.
- [ ] Adapt bootstrap loading for MAC Core's OOP plugin bootstrap.
- [ ] Add `composer test`.
- [ ] Test plugin bootstrap behavior.
- [ ] Test Kernel service registration behavior.
- [ ] Test representative service or utility behavior without brittle WordPress admin UI assertions.

## CI

- [ ] Add a PHP `8.3` Composer-backed GitHub Actions Coding Standards workflow based on `mac-bricks` and `mac-etch`.
- [ ] Ensure CI runs `composer lint`.
- [ ] Ensure CI runs `composer test`.

## Release Archives

- [ ] Update `.github/workflows/release.yml` to exclude dev-only files from release ZIPs.
- [ ] Update `.gitattributes` export ignores for dev-only files.
- [ ] Ensure release archives exclude `AGENTS.md`, `.codex`, `.vscode`, `.ddev`, `composer.json`, `composer.lock`, `phpcs.xml.dist`, `phpunit.xml.dist`, `tests`, `bin/install-wp-tests.sh`, `vendor`, `node_modules`, `.env`, `.env.local`, `.github`, `README.md`, `readme.txt`, `dist`, `coverage`, `.phpunit.cache`, `.phpunit.result.cache`, and `.phpcs-cache`.
- [ ] Keep the `mac-bricks` Composer and PHPCS release exclusions as the baseline, plus the MAC Core plugin-specific PHPUnit exclusions.

## Verification

- [ ] Check `git status` before editing implementation files.
- [ ] Preserve existing user edits, especially in `LICENSE`, `README.md`, `mac-core.php`, and `readme.txt`.
- [ ] Run `composer install`.
- [ ] Run `composer lint`.
- [ ] Run `composer test`.
- [ ] Check release ZIP contents.
