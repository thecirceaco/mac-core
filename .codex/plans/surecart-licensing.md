# SureCart Licensing Plan

## Summary

Implement SureCart-backed license activation and licensed manual update availability for MAC Core without gating existing plugin services.

## Implementation

- Bundle `surecart/wordpress-sdk` tag `v1.1.2` under `licensing/`, including `src/`, `README.md`, `composer.json`, and `PROVENANCE.md`.
- Add `MacCore\Licensing\LicensingService` as a service class and register it in `src/Kernel.php`.
- On `init`, load `licensing/src/Client.php` only if `MacCore\Vendor\SureCart\Licensing\Client` is absent.
- Read the public token from `MAC_CORE_SURECART_PUBLIC_TOKEN`, then pass it through `mac_core_surecart_public_token`.
- If the token is blank, skip SDK initialization and register a `manage_options` admin notice.
- If the SDK file is missing or the SDK class is still unavailable after loading, skip initialization and register a `manage_options` admin notice.
- When configured, instantiate `MacCore\Vendor\SureCart\Licensing\Client` with name `MAC Core`, the public token, and `MAC_CORE_PATH . 'mac-core.php'`.
- Set SDK textdomain to `mac-core`.
- Register the SDK built-in top-level admin license page with page title `MAC Core License`, menu title `MAC Core`, capability `manage_options`, menu slug `mac-core`, the MAC fill logomark icon, and default position.
- Keep `DisableAutoUpdates` unchanged. Licensing should expose manual update availability through the plugin update transient and plugin information hooks, not enable automatic updates.

## Release Metadata

- Add root `release.json` with slug `mac-core`, synchronized plugin version metadata, WordPress requirement `6.9`, tested up to `6.9`, PHP requirement `8.3`, Circea author metadata, description, changelog, and FAQ sections.
- Keep `release.json` synchronized whenever `mac-core.php`, `inc/constants.php`, or `readme.txt` version/platform metadata changes.
- Ensure release ZIPs include `licensing/` and `release.json`, while excluding agent/dev/test files.
- Publish a matching SHA-256 checksum file and provenance JSON for each GitHub release ZIP.

## Verification

- Validate `release.json` as JSON and confirm slug `mac-core`.
- Run `php -l` on `src/Licensing/LicensingService.php` and the bundled SDK runtime files.
- Confirm `git check-attr export-ignore` does not mark `release.json` or `licensing/src/Client.php` as excluded.
- Confirm release workflow excludes dev-only files but does not exclude `release.json` or `licensing/`.
- During the future full tooling pass, run `composer install`, `composer lint`, `composer test`, and a release ZIP contents check.
