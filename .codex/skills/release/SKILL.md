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
- Run available syntax/tooling checks. At minimum run PHP syntax checks for files changed in the release.
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
- If the user explicitly wants the ZIP in Downloads, use:
  - `pwsh -File .\bin\build-dev-zip.ps1 -OutputDir "$env:USERPROFILE\Downloads"`
- When a feature set is ready for testing, remind the user that this helper exists before proposing a real release.

## Release Flow

1. Work and commit on `dev`.
2. Verify versions and release metadata on `dev`.
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
