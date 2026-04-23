# MAC Core Agent Notes

## Scope

These instructions apply to the entire `mac-core` repository.

## Architecture Guardrail

MAC Core must remain OOP. Do not port the procedural organization from the `mac-bricks` or `mac-etch` child themes into this plugin.

- Keep plugin code under the `MacCore` namespace.
- Keep `mac-core.php` as a minimal bootstrap: plugin metadata, `declare(strict_types=1)`, the `ABSPATH` guard, includes for `inc/constants.php` and `inc/autoload.php`, and `\MacCore\Kernel::boot()`.
- Keep `src/Kernel.php` responsible for service loading.
- Keep `MacCore\Contracts\Service` as the service contract for hook-registering services.
- Add WordPress behavior as service classes inside the module folders under `src/` such as `src/Admin`, `src/Licensing`, and `src/Policies/...`; each service must implement `MacCore\Contracts\Service`, register hooks inside `register()`, and be added to the Kernel service list.
- Add shared pure helpers under `src/Utils/...` when they do not need to register hooks.

## Agent Context

Detailed implementation plans, todos, skills, and working context live under `.codex/`. Start there before working on the alignment backlog.

- Keep repo-local AI docs, durable agent notes, and reusable skills under `.codex/`.
- Use the standard Codex skill layout for repo-local skills: `.codex/skills/<skill-name>/SKILL.md`.
- Do not create additional repo-local AI guidance files outside `.codex/`, except for this root `AGENTS.md` entrypoint.

## Editing Discipline

Before editing implementation files, check `git status` and preserve user changes. If `LICENSE`, `README.md`, `mac-core.php`, or `readme.txt` contain uncommitted edits, build on them instead of replacing them.
