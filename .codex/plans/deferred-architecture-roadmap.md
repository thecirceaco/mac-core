# Deferred Architecture Roadmap

These items are intentionally documented now and deferred until MAC Core has enough product surface to justify them.

## Lifecycle And Migrations

Status: Deferred until after settings ship.

When to add:

- MAC Core stores structured versioned settings.
- A release needs to rename keys, backfill defaults, or reshape stored data.

Expected shape:

- `src/Lifecycle`
- `src/Migrations`
- stored version key such as `mac_core_db_version`
- lock key such as `mac_core_migration_lock`
- idempotent ordered migration classes
- admin-visible failure path

## Feature Flags

Status: Deferred. Not needed for a small stateless plugin.

If needed later:

- implement a typed internal feature service
- keep flags under `mac_core_features` or another prefixed internal structure
- do not expose unsafe engineering flags as client-editable settings
- use flags for staged rollout, beta features, or kill switches

## Runtime Composer Dependencies

Status: Deferred.

Definition:

- third-party PHP packages that customer sites need at runtime
- shipped in release ZIPs through Composer `vendor/`
- loaded through Composer's runtime autoloader

Current MAC Core state:

- Composer is dev tooling only
- SureCart SDK is vendored directly under `licensing/`
- release ZIPs do not need `vendor/`

If needed later:

- add production runtime dependencies to Composer
- update release workflow to run `composer install --no-dev --optimize-autoloader`
- include production `vendor/` in the release ZIP

## REST, Assets, and CLI Modules

Status: Deferred until real surfaces exist.

Add only when MAC Core actually needs them:

- `src/Rest`
- `src/Assets`
- `src/Cli`

Do not create these folders early just to look scalable.

## Naming Convention

All persistent keys must use `mac_` prefixes:

- core plugin keys: `mac_core_*`
- future add-on keys: `mac_{addon}_*`
