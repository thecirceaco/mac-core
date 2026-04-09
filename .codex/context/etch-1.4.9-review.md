# Etch 1.4.9 Review For MAC Core

Source reviewed: `D:/business/resources/wp/plugins/core/etch-1.4.9.zip`

## Why This Matters

Etch is not a direct architecture target for MAC Core. It is a larger product plugin with a builder UI, REST API, bundled frontend app, migrations, feature flags, logging, and runtime Composer dependencies. MAC Core should stay smaller, OOP, and service-oriented.

The useful takeaway is not "copy Etch". The useful takeaway is that Etch has several mature product-plugin patterns MAC Core should adopt when they fit.

## What Etch Does Well

### Composer Autoloading And Dependency Packaging

Etch ships `vendor/autoload.php` and Composer-generated PSR-4 mappings:

- `Etch\\` maps to `classes/`
- `Etch\\Includes\\` maps to `includes/`
- Runtime dependencies map to their own namespaces under `vendor/`

MAC Core adaptation:

- Add root Composer tooling now for PHPCS and PHPUnit.
- Keep MAC Core's current lightweight runtime autoloader until runtime dependencies justify shipping `vendor/`.
- If MAC Core later adds runtime dependencies, switch release packaging to run `composer install --no-dev --optimize-autoloader` and include production `vendor/` in the ZIP.
- Keep dev dependencies excluded from release ZIPs.

### Namespaced Vendored SureCart SDK

Etch prefixes the SureCart SDK under `Etch\Includes\SureCart\Licensing`, avoiding collisions with another plugin that may load `SureCart\Licensing\Client`.

MAC Core adaptation:

- MAC Core already vendors the SDK under `MacCore\Vendor\SureCart\Licensing`.
- Keep that namespace isolation in place to avoid collisions with other plugins shipping the SureCart SDK.
- Keep the SDK provenance note and make any future vendor refreshes repeatable.

### Clear Product Modules

Etch groups implementation into module folders such as:

- `Assets`
- `Blocks`
- `Cli`
- `CustomFields`
- `Filters`
- `Helpers`
- `Lifecycle`
- `Migrations`
- `RestApi`
- `Services`
- `Vite`
- `WpAdmin`

MAC Core adaptation:

- Keep hook-registering services in module folders such as `src/Admin`, `src/Licensing`, and `src/Policies/...`.
- Keep `src/Utils` for pure/static helpers that do not register hooks.
- Add future module folders only when a real boundary appears:
  - `src/Rest` for REST route classes if MAC Core ever exposes REST endpoints.
  - `src/Assets` when the plugin owns frontend/admin asset registration.
  - `src/Lifecycle` and `src/Migrations` before storing structured options or running DB migrations.
  - `src/Security` only for cross-cutting security policies, not as a dumping ground.

### REST Route Base Class

Etch has a `RestApi/Routes/BaseRoute.php` with a route-definition pattern and default permission callbacks.

MAC Core adaptation:

- Do not add REST infrastructure until there is a concrete REST endpoint.
- If REST endpoints are added, use a base route or route registrar with explicit permission callbacks on every route.
- Default to least privilege. Avoid broad `edit_posts` read access unless a route truly exposes only editor-safe data.

### Lifecycle And Migration Manager

Etch has an update manager that tracks a DB version, uses a transient lock, and fires update lifecycle hooks. This is useful for product plugins with stored structured data.

MAC Core adaptation:

- Do not add a migration system until MAC Core stores its own structured options, custom tables, or versioned data.
- Before adding such data, add `src/Lifecycle` with:
  - stored DB/schema version,
  - transient lock,
  - idempotent migration classes,
  - admin error notice on migration failure,
  - tests for migration ordering and failure behavior.

### Feature Flags

Etch uses a feature flag library with production flags plus optional dev/user overrides.

MAC Core adaptation:

- Feature flags are likely overkill now.
- If MAC Core starts shipping experimental or staged features, use a small typed config/feature service first.
- Avoid letting client-editable flags enable unsafe code paths.

### Tests In Product Modules

Etch packages many `Tests` directories inside module folders. The presence of tests near behavior is a good sign, even though release ZIP packaging could be leaner.

MAC Core adaptation:

- Prefer a root `tests/` tree for MAC Core so release packaging can exclude it cleanly.
- Add focused tests for utilities, service hook registration, licensing setup behavior, and any future migration/REST layer.

## What MAC Core Should Not Copy

### Procedural Main Plugin File

Etch's `etch.php` includes substantial procedural setup, default data seeding, and migration helper functions.

MAC Core must not copy that. Keep `mac-core.php` minimal and push behavior into services/classes.

### Singleton-First Architecture

Etch uses a `Singleton` trait heavily. It works for Etch, but MAC Core should avoid spreading global state unless there is a real need.

MAC Core should prefer:

- `Kernel` service registration,
- small service objects,
- explicit dependencies when needed,
- static utility classes only for pure helpers.

### Shipping Non-Runtime Docs And Tests In Release ZIPs

Etch includes some dependency READMEs, changelogs, and tests in the ZIP. That is common but not ideal for MAC Core's lean release target.

MAC Core should:

- exclude root AI/dev docs,
- exclude test harness and dev tooling,
- exclude vendored non-runtime documentation where license compliance allows,
- include runtime SDK code and required runtime metadata only.

## Future Implementation Priorities

1. Add Composer dev tooling now.
2. Add the PHPUnit harness now.
3. Add CI for `composer lint` and `composer test`.
4. Add release checksums/provenance before the next SureCart upload workflow matures.
5. Consider prefixing the SureCart SDK namespace if collision risk becomes important.
6. Add lifecycle/migration scaffolding before MAC Core stores versioned data.
7. Add REST/Admin module boundaries only when those surfaces exist.

## Design Rule

Structure MAC Core for growth without adding empty abstractions. Add each folder or subsystem when it owns a real capability, and keep every new subsystem behind a small, testable class boundary.
