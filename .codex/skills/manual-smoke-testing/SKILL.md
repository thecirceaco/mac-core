# MAC Core Manual Smoke Testing Skill

Use this skill whenever a `MAC Core` change affects the helper surface, `FormatDatetime`, child-theme extension seams, or the local WordPress QA flow.

## When To Use It

- A public helper wrapper is added, removed, renamed, or changes behavior.
- A helper toggle or helper gating behavior changes in the `Helpers` tab.
- `FormatDatetime` presets, views, defaults, or supported filters change.
- A Bricks-facing smoke snippet or recommended QA path becomes stale.
- A release is being prepared and the local WordPress smoke path must be confirmed current.

## Required Workflow

1. Read the current runbook first:
   - `.codex/context/manual-smoke-testing/runbook.md`
   - `.codex/context/manual-smoke-testing/acf-import.json`
   - `.codex/context/manual-smoke-testing/release-matrix.md`
2. Keep the runbook current when any of these change:
   - helper names or toggles
   - `FormatDatetime` field expectations
   - `mac_core_format_datetime_config`
   - `mac_core_format_datetime_presets`
   - `mac_core_format_datetime_views`
   - stored ACF import fixture content for the smoke site
   - recommended Bricks or child-theme smoke setup
3. Update the WordPress steps, child-theme snippet, Bricks snippet, and stored ACF import JSON in the same task when they drift.
   - Keep the admin/settings checklist current as a separate part of the runbook.
   - Keep the Bricks snippet clearly scoped to frontend runtime verification.
4. Before release, review this runbook even if no obvious smoke doc file changed during implementation.
5. When the current release requires human QA, explicitly tell the user:
   - why human QA is required
   - which `.codex` files to use
   - the concrete WordPress steps they should run
   - whether the Bricks snippet alone is sufficient; if settings/admin are in scope, explicitly say no
   - to copy-paste the full rendered smoke-test output back into the thread after running it
6. Use the release matrix to scale the QA ask:
   - patch: targeted human QA only when runtime behavior changed
   - minor: human QA always required
   - major: broader human QA always required
7. Pair this with:
   - `.codex/skills/dev-zip-testing/SKILL.md` when building a local QA ZIP
   - `.codex/skills/ai-context-sync/SKILL.md` when repo-local context may also be stale
   - `.codex/skills/mac-docs-sync/SKILL.md` if the user-facing docs also need to describe the changed helper behavior

## Important Rules

- Do not leave the Bricks smoke snippet stale after helper or `FormatDatetime` changes.
- Keep the runbook single-site unless official multisite support is added.
- Keep all code samples aligned with the current shipped helper names and hook names.
- Prefer updating the durable runbook in `.codex/context/` instead of relying on a transient thread message.
- Do not make the user guess whether human QA is required for a release. State it directly.
- Prefer enough `FormatDatetime` sample calls in the Bricks snippet to cover plain, relative, diff, HTML, timezone, custom-view, and default-config behavior.
- Keep `mac_core_get_post_terms()` smoke examples aligned with the current wrapper-class behavior and default `mac-core-terms*` markup.
- Keep `mac_core_format_price()` smoke examples aligned with the current `plain`, `html`, and `raw` contract and the default `mac-core-price*` markup.
- When `FormatDatetime` parsing or precedence changed, keep the runbook aligned for separate-field, combined-datetime, and mixed-source precedence checks.
- Make it explicit that the Bricks snippet validates frontend helper/runtime behavior against the saved helper state. It does not prove admin tab routing, settings save behavior, or non-helper settings behavior.
