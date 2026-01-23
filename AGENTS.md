## Project Overview

- Project name: `mac-core`
- Project type: WordPress plugin
- Purpose: Standard functionality plugin used across starter WordPress templates
- Ownership: Company-owned (`thecirceaco`)
- Namespace root: `MacCore\`
- PHP version target: >= 8.0
- Architecture: Object-oriented, service-based
- Scope: Applies ONLY to the `mac-core` plugin

This plugin is infrastructure code.

It must be stable, predictable, and reusable across multiple projects.

## Core Philosophy

All code must prioritize:

1. Security
2. Correctness
3. Maintainability
4. Scalability
5. WordPress standards + modern PHP

If there is ever a trade-off, choose clarity and safety over cleverness.

## Non-Negotiable Rules

These rules are mandatory.

Do not improvise or deviate without explicit instruction.

## Architecture Rules

### Service-based architecture (mandatory)

- Every feature is implemented as a Service
- Every service:
    - is a class
    - implements:

        ```php
        MacCore\Contracts\Service
        ```

    - exposes exactly:

        ```php
        publicfunctionregister():void;
        ```

- Services:
    - MUST NOT execute logic in constructors
    - MUST register all hooks inside `register()`
    - MUST NOT perform bootstrapping

### Kernel / Bootstrap Rules

- The plugin has a single entry flow
- Services are:
    - instantiated only in the Kernel
    - registered centrally

Kernel responsibilities:

- instantiate services
- call `$service->register()`
- contain no business logic

## Autoloading Rules

- Custom autoloader using `spl_autoload_register`
- PSR-4–style mapping:

```php
MacCore\Foo\Bar →src/Foo/Bar.php
```

Rules:

- Autoloader must bail early if namespace does not match
- Autoloader must include only readable files
- No silent failures for core logic
- Composer is not used unless explicitly decided later

## PHP File Structure Rules (VERY IMPORTANT)

### 1. Non-namespaced files (entry / executable files)

Examples:

- plugin main file
- bootstrap files
- files loaded directly by WordPress

Required order:

```php
<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) exit;
```

Rules:

- `declare(strict_types=1);` MUST be the first statement
- ABSPATH guard is REQUIRED
- No namespace allowed

### 2. Namespaced files (classes, services, Kernel)

Examples:

- `src/Kernel.php`
- `src/Services/*.php`
- `src/Contracts/*.php`

Required order:

```php
<?php
declare(strict_types=1);

namespaceMacCore\Some\Namespace;

if ( ! defined( 'ABSPATH' ) ) exit;
```

Rules:

- `declare(strict_types=1);` MUST be first
- `namespace` MUST be second
- ABSPATH guard comes after the namespace
- ABSPATH guard:
    - REQUIRED for entry-like or executable files
    - OPTIONAL (and usually omitted) for pure class definitions

### 3. Pure definition files

Examples:

- interfaces
- value objects
- enums

Rules:

- `declare(strict_types=1);` required
- namespace required
- ABSPATH guard usually omitted

## ABSPATH Guard Decision Matrix

Use the guard only when it makes sense.

### Use ABSPATH guard in:

- plugin entry file
- bootstrap files
- files that execute logic immediately

### Do NOT use ABSPATH guard in:

- service classes
- interfaces
- Kernel (unless it executes logic directly)

If unsure: do not add a guard. Ask or default to minimal.

## Coding Standards

### PHP formatting

- Indentation: 4 spaces
- Tabs: spaces only
- Line endings: Unix (LF)
- Braces: PSR-12 style
- Visibility: always explicit (`public`, `private`, `protected`)

### Quotes

- Follow WordPress Coding Standards
- Prefer single quotes
- Use double quotes only when interpolation is required

### DocBlocks

DocBlocks are required for:

- all classes
- all interfaces
- public methods
- public properties
- typed properties when non-obvious
- complex arrays (`array<int,string>`, etc.)

DocBlocks must be:

- accurate
- minimal
- never redundant
- WordPress-compatible

## Namespaces & Global Functions

Inside namespaced files:

- WordPress functions MUST be prefixed with `\`

Example:

```php
\add_action(...)
\get_option(...)
```

PHP built-ins should also be prefixed when clarity matters:

```php
\file_exists()
\is_readable()
```

## Security Rules

Always enforce:

- no direct file access
- least privilege
- no unvalidated input
- no silent fallbacks for core logic
- no global state mutation without WordPress APIs
- no direct database access unless explicitly required

## Error Handling Philosophy

- Fail early
- Fail explicitly
- Never hide fatal configuration errors
- Deprecated notices from third-party plugins are not fixed here

## Forbidden Actions

Do NOT:

- introduce procedural logic into services
- bypass the Kernel
- add global helper functions
- remove `strict_types`
- reorder `declare`, `namespace`, or guards
- mix theme assumptions into this plugin
- add guards blindly

## Decision Priority (when uncertain)

Always decide in this order:

1. WordPress Coding Standards
2. Modern PHP best practices
3. Explicit, readable code over clever abstractions
4. Security > scalability > convenience

## Final Note

This file is authoritative.

If instructions conflict with intuition, follow this file.

Violating these rules is considered a bug, not a style preference.