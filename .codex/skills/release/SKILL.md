# MAC Core Release Skill

Use this skill whenever preparing, checking, tagging, or publishing a MAC Core release.

## Branch Discipline

- Always do normal work on `dev`.
- Always switch back to `dev` after release work is finished.
- Keep `main` squeaky clean.
- Use `main` only for the release/tag workflow.
- Do not continue feature, documentation, or cleanup work on `main` after a release.
- Before release, make sure the working tree is clean on `dev`.
- Promote `dev` to `main` only when the release commit is ready and verified.
- After tagging and pushing the release from `main`, switch back to `dev` before ending the task or starting more work.

## Version Locations

Check and update every MAC Core plugin version carrier before release:

- `mac-core.php`: plugin header `Version:`.
- `inc/constants.php`: `MAC_CORE_VERSION`.
- `readme.txt`: `Stable tag:`.
- `readme.txt`: matching top changelog heading, for example `= 0.5.0 =`.
- `release.json`: root `version`.
- `release.json`: `sections.changelog` heading/content.

Also check platform compatibility metadata when relevant:

- `mac-core.php`: `Requires PHP:` and `Requires at least:`.
- `readme.txt`: `Requires PHP:`, `Requires at least:`, and `Tested up to:`.
- `release.json`: `requires_php`, `requires`, and `tested`.

## Pre-Release Checks

- Check the latest GitHub release first:
  - `gh release view --json tagName,name,publishedAt,isDraft,isPrerelease,url`
- Check local tags:
  - `git tag --list "v*" --sort=-v:refname`
- Verify the target tag does not already exist locally or remotely.
- Search version carriers:
  - `rg -n 'Stable tag|Version:|MAC_CORE_VERSION|"version"|requires_php|requires|tested' mac-core.php inc readme.txt release.json`
- Validate `release.json` parses as JSON.
- Run the dedicated AI context sync review and update stale `.codex` docs or skills before tagging:
  - `.codex/skills/ai-context-sync/SKILL.md`
- Review the manual smoke-testing runbook and update stale setup steps or snippets before tagging:
  - `.codex/skills/manual-smoke-testing/SKILL.md`
  - `.codex/context/manual-smoke-testing/runbook.md`
  - `.codex/context/manual-smoke-testing/acf-import.json`
  - `.codex/context/manual-smoke-testing/release-matrix.md`
- Confirm repo-local AI docs and skills still follow the expected structure before tagging:
  - only `AGENTS.md` lives at repo root
  - repo-local AI docs live under `.codex/`
  - repo-local skills use `.codex/skills/<skill-name>/SKILL.md`
- Run available syntax/tooling checks. At minimum run PHP syntax checks for files changed in the release.
- Delete generated local artifacts from `dist/` before the real release/tag flow, even though `dist/` is gitignored and excluded from release archives.
- Check `git archive` contents before tagging so release ZIPs include runtime assets and exclude dev-only files.
- Confirm SureCart licensing releases include `release.json` and `inc/Vendor/SureCart/Licensing/`.
- Confirm the release workflow will publish the ZIP, matching `.sha256` checksum file, and provenance JSON.
- Confirm agent/dev files such as `.codex/`, `AGENTS.md`, `.github/`, root `README.md`, root `readme.txt`, root Composer/PHPCS/PHPUnit files, tests, caches, and local environment folders are excluded from release ZIPs.

## Local Dev ZIPs

- For local testing on `dev`, build a ZIP from the current committed `HEAD` without touching `main` or creating a tag.
- Use the dedicated dev ZIP testing skill when the user asks to test a plugin version before release.
- Use:
  - `pwsh -File .\bin\build-dev-zip.ps1`
- The script creates `dist/mac-core-dev-<shortsha>.zip`.
- The `<shortsha>` portion is the current commit hash, for example `f1ac6cb`.
- The script uses `git archive`, so it respects `.gitattributes` export-ignore rules.
- Before creating the new ZIP, the script removes older `mac-core-dev-*.zip` files from the chosen output folder.
- By default the script refuses to run on a dirty worktree because uncommitted changes are not included in `git archive`.
- Only use `-AllowDirty` if you explicitly want a ZIP from the last commit while ignoring local uncommitted edits.
- After building the ZIP for QA, use the manual smoke-testing runbook on a single-site WordPress test install.
- If the user explicitly wants the ZIP in Downloads, use:
  - `pwsh -File .\bin\build-dev-zip.ps1 -OutputDir "$env:USERPROFILE\Downloads"`
- When a feature set is ready for testing, remind the user that this helper exists before proposing a real release.

## Release Tier QA Rules

Classify the release before signoff:

- `patch`
  - targeted bugfix, internal cleanup, copy tweak, or low-risk behavior correction
  - human QA is required if shipped runtime behavior changed
- `minor`
  - new feature, new setting, new helper, new hook, or admin IA expansion
  - human QA is always required
- `major`
  - breaking change, public contract shift, settings/default/upgrade impact, or other high-risk release
  - human QA is always required at the broadest level

Do not under-test a patch that touches high-risk areas. If the changed surface is licensing, updates, settings persistence, helper output, builder behavior, install/upgrade flow, or packaging, use at least the relevant human QA from the higher-risk bar.

Use the detailed matrix here:

- `.codex/context/manual-smoke-testing/release-matrix.md`

## Dist Cleanup Rule

- `dist/` is local-only build output.
- Keep it gitignored.
- Keep it excluded from release archives.
- Before a real release or tag, delete generated artifacts from `dist/` anyway so stale local ZIPs do not confuse release prep or handoff.

## User Handoff Requirement

Before wrapping a release-prep task, explicitly tell the user:

- which release tier applies
- whether human QA is required
- why
- which `.codex` files to use
- the exact WordPress test steps when human QA is required

If helper or builder behavior is in scope, point them to:

- `.codex/context/manual-smoke-testing/acf-import.json`
- `.codex/context/manual-smoke-testing/runbook.md`

## Post-Release Docs Follow-Up

- After the MAC Core release is finalized for real, review `mac-docs` again even if the main MAC Core pages were already updated during implementation.
- Use the relevant `mac-docs` repo skills/workflows there rather than improvising the docs process from this repo.
- Create or update the matching `mac-docs` release-log entry for that version, for example the MAC Core `v1.0.0` entry under `log/`.
- Follow the existing style and structure used by other product release/version entries in `mac-docs`.
- Keep the `mac-docs` release-log sidebar newest-first, so a newer version page sits above the older one.

## Release Flow

1. Work and commit on `dev`.
2. Verify versions, release metadata, and repo-local AI context on `dev`.
3. Switch to `main`.
4. Fast-forward `main` from `dev`; do not merge with a non-fast-forward release merge unless explicitly requested.
5. Create the release tag on `main`, for example `v0.5.0`.
6. Push `dev`, `main`, and the tag.
7. Verify the tag-triggered GitHub Actions release workflow succeeds.
8. Verify the GitHub release exists and the ZIP, checksum, and provenance assets uploaded.
9. Switch back to `dev`.
10. Confirm `git status --short` is clean.

## Notes

- If Git commit or tag signing fails because the sandbox cannot access the configured signer, retry the single command with signing disabled for that command only, for example `git -c commit.gpgsign=false commit ...` or `git -c tag.gpgsign=false tag ...`.
- Do not change global or repo signing configuration unless the user explicitly asks.
- Do not leave the thread on `main`.
