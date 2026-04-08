# WordPress Plugin OOP Skill

Use this skill when adding or changing MAC Core WordPress behavior.

## Core Rules

- Keep plugin code under the `MacCore` namespace.
- Keep `mac-core.php` minimal. It should contain plugin metadata, `declare(strict_types=1)`, the `ABSPATH` guard, includes for `inc/constants.php` and `inc/autoload.php`, and `\MacCore\Kernel::boot()`.
- Keep `src/Kernel.php` responsible for service loading.
- Keep hook-registering services behind `MacCore\Contracts\Service`.
- Do not port procedural organization from `mac-bricks` or `mac-etch`.

## Adding WordPress Behavior

- Create a service class under `src/Services/...`.
- Make the service implement `MacCore\Contracts\Service`.
- Register all WordPress hooks inside the service's `register()` method.
- Put callback methods on the service class.
- Add the service instance to the Kernel service list.

## Adding Shared Helpers

- Put pure helpers under `src/Utils/...` when they do not need to register WordPress hooks.
- Keep helpers deterministic where practical and avoid hidden WordPress global dependencies unless the helper's purpose requires them.
- If a helper needs a global template function wrapper, keep the wrapper small and delegate to the namespaced utility class.

## Before Editing

- Run `git status`.
- Preserve user changes. If an implementation file already contains uncommitted edits, build on the existing content instead of replacing it.
