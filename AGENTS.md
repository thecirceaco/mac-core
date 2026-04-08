# MAC Core Agent Notes

## Scope

These instructions apply to the entire `mac-core` repository. This file documents the follow-up work required to align MAC Core with `mac-bricks` and `mac-etch`; it is not the implementation of that work.

## Architecture Guardrail

MAC Core must remain OOP, unlike the more procedural `mac-bricks` and `mac-etch` child themes.

- Keep plugin code under the `MacCore` namespace.
- Keep `mac-core.php` as a minimal bootstrap: plugin metadata, `declare(strict_types=1)`, the `ABSPATH` guard, includes for `inc/constants.php` and `inc/autoload.php`, and `\MacCore\Kernel::boot()`.
- Keep `src/Kernel.php` responsible for service loading.
- Keep `MacCore\Contracts\Service` as the service contract for hook-registering services.
- Add new WordPress behavior as service classes under `src/Services/...`; each service should implement `MacCore\Contracts\Service`, register hooks inside `register()`, and be added to the Kernel service list.
- Add shared pure helpers under `src/Utils/...` when they do not need to register hooks.
- Do not port the procedural theme organization from `mac-bricks` or `mac-etch` into MAC Core.

## Alignment Checklist

Future implementation should align MAC Core with the current `mac-bricks` and `mac-etch` baseline while preserving the OOP architecture above.

- Update platform metadata to require PHP `8.3`, require WordPress `6.9`, and mark tested up to WordPress `6.9`.
- Keep the project on GPL v3 or later.
- Reconcile version metadata across `mac-core.php`, `MAC_CORE_VERSION` in `inc/constants.php`, `readme.txt` stable tag, and changelog entries before release.
- Add Composer dev tooling that mirrors the theme repos:
  - `dealerdirect/phpcodesniffer-composer-installer`
  - `squizlabs/php_codesniffer`
  - `wp-coding-standards/wpcs`
  - `composer lint` running `phpcs --standard=phpcs.xml.dist`
  - `composer lint:fix` running `phpcbf --standard=phpcs.xml.dist`
- Add `phpcs.xml.dist` for the plugin, scoped to `mac-core.php`, `index.php`, `uninstall.php`, `inc`, and `src`.
- Exclude `vendor/*` and `.codex/*` from PHPCS.
- Add the same PHP `8.3` Composer-backed GitHub Actions Coding Standards workflow used by `mac-bricks` and `mac-etch`.
- Update the release ZIP workflow and `.gitattributes` export ignores so dev-only files stay out of release archives, including `AGENTS.md`, `.codex`, `composer.json`, `composer.lock`, `phpcs.xml.dist`, `vendor`, `node_modules`, `.env`, `.env.local`, `.github`, `README.md`, `readme.txt`, and `dist`.

## PHPUnit Requirement

Add a WordPress plugin PHPUnit harness for MAC Core even though no PHPUnit suite was found in `mac-bricks` or `mac-etch` during planning.

- Prefer the WP-CLI plugin test scaffold as the starting point: https://developer.wordpress.org/cli/commands/scaffold/plugin-tests/
- Adapt the scaffold for Composer-based dependencies and the MAC Core OOP bootstrap.
- Use WP PHPUnit documentation for the Composer-compatible test library setup: https://github.com/wp-phpunit/docs
- Add `composer test`.
- Include CI coverage for both `composer lint` and `composer test`.
- Cover at minimum the plugin bootstrap, Kernel service registration behavior, and representative service or utility behavior that can be tested without brittle WordPress admin UI assertions.

## Implementation Notes

- Before editing implementation files, check `git status` and preserve any user changes. If `LICENSE`, `README.md`, `mac-core.php`, or `readme.txt` already contain uncommitted edits, build on them instead of replacing them.
- For this AGENTS-only change, verification is limited to confirming this file exists and includes the OOP guardrail, metadata checklist, PHPCS/CI/release checklist, and PHPUnit requirement.
- For the future implementation pass, verify with `composer install`, `composer lint`, `composer test`, and a release ZIP contents check.
