# Automatic.css 4.0.0 RC1 Review For MAC Core

Source reviewed: `D:/business/resources/wp/plugins/core/automatic.css-4.0.0-rc-1.zip`

## Why This Matters

Automatic.css is a larger product plugin than MAC Core, but it has several mature patterns that fit MAC Core's intended direction: a top-level admin page with routed subviews, a settings repository over one WordPress option, lifecycle and migration management once real settings data exists, and a clear split between product modules.

## Useful Patterns To Adapt

### Top-level admin page with subviews

- ACSS uses one top-level page and then routes to specific screens.
- MAC Core should keep one top-level page at `page=mac-core`.
- The base route should redirect to `tab=settings` until a real Welcome screen exists.
- Secondary views use `tab=settings`, `tab=license`, `tab=support`, and later add-on views.

### Settings repository over one option

- ACSS stores settings in one option and reads them through a repository abstraction.
- MAC Core should store settings in one option: `mac_core_settings`.
- Use nested module data inside that option:
  - `core`
  - `media`
  - future add-on namespaces
- Use native WordPress option serialization, not JSON blobs.

### Lifecycle and migration manager

- ACSS has a dedicated lifecycle/update manager with migration ordering and locking.
- MAC Core should add this only after the settings system exists and before any schema-changing settings release.
- Future MAC Core lifecycle keys should follow the prefix rule:
  - `mac_core_db_version`
  - `mac_core_migration_lock`

### Integrations/module manager

- ACSS has a clear integrations layer for optional product surfaces.
- MAC Core should stay lighter, but the equivalent future pattern is:
  - `mac_core_services`
  - `mac_core_admin_tabs`
  - `mac_core_settings_sections`
- This is enough to support future add-ons without building a full add-on SDK now.

## What Not To Copy

- Do not copy ACSS's singleton-heavy container architecture.
- Do not copy the broader framework complexity before MAC Core needs it.
- Do not create empty folders just because ACSS has them.
- Do not let UI structure dictate storage keys; keep storage module-based, not tab-based.

## Resulting MAC Core Direction

- Module folders under `src/`:
  - `Admin`
  - `Licensing`
  - `Settings`
  - `Policies/Core`
  - `Policies/Media`
- One settings repository.
- One top-level admin page.
- One light extension seam for future add-ons.
- Deferred lifecycle/migrations after settings exist.
