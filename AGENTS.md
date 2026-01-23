This file is authoritative for the `mac-core` WordPress plugin.

If something is not explicitly allowed here, assume it is not allowed.

`mac-core` is **infrastructure code** used across many client sites.

It must be boring, stable, predictable, and safe.

## Project Facts

- Plugin name: `mac-core`
- Type: WordPress plugin
- Ownership: Company-owned (`thecirceaco`)
- Namespace root: `MacCore\`
- PHP target: >= 8.0
- Scope: **ONLY** this plugin

This plugin is **not** a theme and must not assume theme behavior.

## Core Principles (in priority order)

1. Security
2. Correctness
3. Maintainability
4. Scalability
5. WordPress standards + modern PHP

When in doubt, choose **clarity and safety over cleverness**.

## Architecture Rules (mandatory)

### Service-based architecture

- Every feature is a **Service**
- Every service:
    - is a class
    - implements `MacCore\Contracts\Service`
    - exposes **only**:

        ```php
        public function register(): void;
        ```


Rules:

- Services MUST NOT execute logic in constructors
- Services MUST register all hooks inside `register()`
- Services MUST NOT bootstrap other services

### Kernel rules

- There is exactly **one entry flow**
- The Kernel:
    - instantiates services
    - calls `$service->register()`
- The Kernel contains **no business logic**

No service may self-register or bypass the Kernel.

## Autoloading

- Custom autoloader using `spl_autoload_register`
- PSR-4–style mapping:

    ```
    MacCore\Foo\Bar → src/Foo/Bar.php
    ```


Rules:

- Bail early if namespace does not match
- Include only readable files
- No silent failures for core logic
- Composer is NOT used unless explicitly decided later

## PHP File Rules (very important)

### Non-namespaced files

(plugin entry file, bootstrap files, files loaded directly by WordPress)

Required order:

```php
<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) exit;
```

Rules:

- `declare(strict_types=1);` MUST be first
- ABSPATH guard is REQUIRED
- No namespace allowed

### Namespaced files

(classes, services, Kernel)

Required order:

```php
<?php
declare(strict_types=1);

namespace MacCore\Some\Namespace;
```

Rules:

- `declare(strict_types=1);` MUST be first
- `namespace` MUST be second
- ABSPATH guard:
    - REQUIRED only for entry-like or executable files
    - usually omitted for pure class definitions

If unsure, omit the guard.

### Pure definition files

(interfaces, value objects, enums)

Rules:

- `declare(strict_types=1);` required
- namespace required
- ABSPATH guard usually omitted

## WordPress & PHP usage rules

Inside namespaced files:

- WordPress functions MUST be prefixed with `\`

```php
\add_action(...)
\get_option(...)
```

- PHP built-ins should be prefixed when clarity matters:

```php
\file_exists()
\is_readable()
```

## Coding standards

- Indentation: 4 spaces
- Tabs: spaces only
- Line endings: LF
- Braces: PSR-12
- Visibility: always explicit

Quotes:

- Prefer single quotes
- Use double quotes only when interpolation is required

DocBlocks are required for:

- all classes
- all interfaces
- public methods
- public properties
- complex or non-obvious types

DocBlocks must be accurate, minimal, and WordPress-compatible.

## Security rules (mandatory)

Always enforce:

- no direct file access
- least privilege
- no unvalidated input
- no silent fallbacks for core logic
- no global state mutation outside WordPress APIs
- no direct database access unless explicitly required

Fail early. Fail explicitly.

## Licensing & Updates (important)

### Licensing model

- `mac-core` **uses license keys**
- Licensing authority is **SureCart**
- SureCart is the **source of truth** for:
    - license validity
    - activations
    - expiration
    - entitlements

`mac-core` implements a **License Service** that:

- stores the license key locally
- communicates with the SureCart API (directly or via SDK)
- caches license state
- exposes normalized states internally

License states are explicit and finite (e.g. valid, expired, invalid, unknown).

### Update model (Etch-style)

- Updates are gated by license validity
- License checking and updating are **separate concerns**

`mac-core` implements:

- a **License Service** (no update logic)
- an **Updater Service** (no license validation logic)

Updater rules:

- Uses WordPress’ native plugin update system
- Checks the License Service before offering updates
- Does NOT talk directly to SureCart
- Does NOT implement custom update UI
- Does NOT break the plugin if the license is invalid or expired

Expired or invalid license:

- no updates
- plugin continues to function

Update artifacts may be hosted via GitHub Releases or a private endpoint.

## Forbidden actions

Do NOT:

- add procedural logic to services
- bypass the Kernel
- add global helper functions
- remove `strict_types`
- reorder `declare`, `namespace`, or guards
- mix theme assumptions into this plugin
- scatter license checks across services
- couple updater logic directly to SureCart

Violations are bugs, not style preferences.

## Decision rule

If unsure, decide in this order:

1. WordPress Coding Standards
2. Modern PHP best practices
3. Explicit, readable code
4. Security over convenience