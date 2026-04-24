# MAC Core Docs Sync Skill

Use this skill whenever a `MAC Core` change may require an update in `mac-docs`.

## When To Use It

- A public `MAC Core` setting is added, removed, renamed, or changes default behavior.
- A settings-backed policy changes admin behavior, editor behavior, or starter guidance.
- The `MAC Core` admin UI, tabs, plugin links, licensing flow, or onboarding steps change.
- A public utility helper, documented hook, or supported extension surface changes.
- A `MAC Starter` recommendation changes because of `MAC Core`.

## Required Workflow

1. Review the official `MAC Core` docs first:
   - [`thecirceaco/mac-docs`](https://github.com/thecirceaco/mac-docs): `sources/doc/mac-core/`
2. Review the official `MAC Core` release history:
   - [`thecirceaco/mac-docs`](https://github.com/thecirceaco/mac-docs): `sources/log/mac-core/`
3. Review the starter integration page:
   - [`thecirceaco/mac-docs`](https://github.com/thecirceaco/mac-docs): `sources/doc/mac-starter/plugins/mac-core/index.md`
4. If the change affects starter conventions, also review the related starter docs:
   - posts, plugins, themes, or settings pages tied to that behavior
5. Search for additional references:
   - in your local `thecirceaco/mac-docs` clone, run `rg -n "mac-core|MAC Core" sources`
6. Update `mac-docs` in the same task when the change is public-facing.

## Important Rules

- Do not leave `mac-docs` stale when `MAC Core` public behavior changes.
- Prefer updating the official product docs and the starter docs in the same change set.
- Prefer updating the official product docs and release log in the same change set when the version is being shipped.
- Pair this review with `.codex/skills/ai-context-sync/SKILL.md` when repo-local agent guidance may also have drifted.
- If a `MAC Core` change creates duplicate-feature ownership rules with another plugin, update the relevant starter plugin pages too.
- Keep docs accurate to the current shipped or intended plugin behavior; do not leave stale version-specific messaging behind.
- Keep `date` frontmatter present on every documented `MAC Core` version page.
