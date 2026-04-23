# MAC Core AI Context Sync Skill

Use this skill whenever a `MAC Core` change may require updates in the repo-local AI context, durable notes, or reusable skills under `.codex/`.

## When To Use It

- A plugin change affects admin routing, tabs, onboarding flow, or settings navigation.
- A plugin change affects settings storage, module structure, defaults, or upgrade behavior.
- A plugin change affects public helper wrappers, documented hooks, or other supported extension surface.
- A plugin change affects SureCart licensing, update flow, release metadata, or release packaging.
- A new `.codex` context note or skill is added, removed, renamed, or supersedes an older one.
- A release is being prepared, even if no obvious `.codex` file changed during the feature work.

## Required Workflow

1. Review the local context index first:
   - `D:\business\projects\mac-core\.codex\README.md`
2. Review the `.codex` files most likely touched by the change:
   - relevant files under `context/`
   - relevant files under `plans/` and `reports/` when they describe the affected behavior
   - relevant files under `skills/`
   - `context/manual-smoke-testing/runbook.md`, `context/manual-smoke-testing/acf-import.json`, `context/manual-smoke-testing/release-matrix.md`, and `skills/manual-smoke-testing/SKILL.md` when helper behavior, `FormatDatetime`, or the WordPress smoke path changes
3. Search `.codex` for stale behavior, routing, settings, or release references before finishing:
   - `rg -n "MAC Core|mac-core|tab=|Helpers|Utils|mac_core_settings|MAC_CORE_VERSION|release.json|SureCart|multisite|mac_" D:\business\projects\mac-core\.codex`
4. Confirm repo-local AI docs and skills still live in the expected places:
   - repo-local AI docs and durable notes live under `.codex/`
   - repo-local skills use `.codex/skills/<skill-name>/SKILL.md`
   - the only intended repo-root AI guidance file is `AGENTS.md`
5. Update stale `.codex` files in the same task when the plugin behavior they describe has changed.
6. Update `.codex\README.md` whenever a new durable context file or skill is added, removed, or materially repurposed.
7. If the change is public-facing, pair this review with `skills/mac-docs-sync/SKILL.md`.

## Important Rules

- Do not leave repo-local agent guidance stale after a `MAC Core` behavior change.
- Prefer updating the durable `.codex` source files instead of relying on temporary thread context.
- Do not leave repo-local AI docs or skill files scattered outside `.codex/`.
- Do not create non-standard repo-local skill files; use `.codex/skills/<skill-name>/SKILL.md`.
- If a note is intentionally historical rather than current guidance, label it clearly as historical or superseded instead of leaving it silently stale.
- Release preparation is not complete until the relevant `.codex` docs and skills, including the manual smoke-testing runbook when applicable, have been reviewed and updated if needed.
