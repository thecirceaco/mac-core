# MAC Core Dev ZIP Testing Skill

Use this skill whenever the user wants to test a plugin build locally before a real release, or asks for a ZIP from the current `dev` state.

## When To Use It

- The user wants to test a plugin version on a local/DDEV/staging site.
- A feature set is ready for QA but should not be released yet.
- The user asks for a ZIP from `dev`.
- You are about to hand off a testable change and should remind the user that the local dev ZIP helper exists.

## Command

- Default:
  - `pwsh -NoProfile -File .\bin\build-dev-zip.ps1`
- Optional output directory:
  - `pwsh -NoProfile -File .\bin\build-dev-zip.ps1 -OutputDir "$env:USERPROFILE\Downloads"`
- Escape hatch:
  - `pwsh -NoProfile -File .\bin\build-dev-zip.ps1 -AllowDirty`

## Behavior

- The script builds a ZIP from the current committed `HEAD` using `git archive`.
- Output name:
  - `mac-core-dev-<shortsha>.zip`
- Default output folder:
  - `dist/`
- Before writing the new ZIP, the script removes older `mac-core-dev-*.zip` files from the chosen output folder so local test archives do not pile up.
- The ZIP respects `.gitattributes` export-ignore rules, so dev-only files stay out.
- If you want a one-off ZIP in Downloads instead, use:
  - `pwsh -NoProfile -File .\bin\build-dev-zip.ps1 -OutputDir "$env:USERPROFILE\Downloads"`

## Important Rules

- Prefer a clean worktree before building the ZIP.
- Because `git archive` uses committed content, uncommitted changes are not included.
- Only use `-AllowDirty` when you intentionally want a ZIP from the last commit while ignoring current local edits.
- This helper is for local testing only. It is not the real release flow.
- Real releases still go through `dev` -> `main` -> tag -> GitHub Actions.

## Handoff Reminder

When a plugin version is ready for testing, explicitly tell the user:

- a local dev ZIP can be built without releasing
- this helper can generate it immediately
- it defaults to a self-cleaning `dist/` output folder, with an optional Downloads override if requested
- the exact expected output filename based on the current commit hash
