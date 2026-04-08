# SureCart Licensing Todos

## Documentation

- [x] Capture SureCart licensing setup research.
- [x] Capture SDK internals relevant to MAC Core.
- [x] Document MAC Core-specific licensing decisions.
- [x] Document SDK source, version pin, and provenance.

## SDK

- [x] Bundle `surecart/wordpress-sdk` tag `v1.1.2` under `licensing/`.
- [x] Include SDK runtime files from `src/`.
- [x] Include SDK README and composer metadata.
- [x] Add local SDK provenance notes.

## Integration

- [x] Add `MacCore\Services\Licensing`.
- [x] Register the licensing service in `Kernel`.
- [x] Initialize on `init`.
- [x] Load the SDK only if `SureCart\Licensing\Client` is absent.
- [x] Read token from `MAC_CORE_SURECART_PUBLIC_TOKEN` and `mac_core_surecart_public_token`.
- [x] Skip initialization and show an admin notice when the token is blank.
- [x] Add the SDK built-in top-level license page as `MAC Core`.
- [x] Keep plugin behavior available without an active license.
- [x] Leave `DisableAutoUpdates` unchanged.

## Release

- [x] Add root `release.json`.
- [x] Ensure `release.json` slug is `mac-core`.
- [x] Ensure release workflow keeps `licensing/` and `release.json`.
- [x] Ensure release workflow excludes dev-only files.

## Tests

- [ ] Add PHPUnit coverage for licensing service hook registration.
- [ ] Add PHPUnit coverage for missing-token skip behavior.
- [ ] Add PHPUnit coverage for configured SDK initialization.
- [ ] Add PHPUnit coverage for `release.json` metadata synchronization.

## Verification

- [x] Validate `release.json` as JSON.
- [x] Run PHP syntax checks on MAC Core licensing service and SDK runtime files.
- [x] Confirm `release.json` and `licensing/` are not `export-ignore`.
- [x] Confirm release workflow packaging rules keep licensing artifacts.
- [x] Run `composer install`, `composer lint`, and `composer test` after Composer/PHPUnit tooling exists.
