# Dev Branch Git Discipline

Use this skill before staging, committing, pushing, or releasing MAC Core work.

## Branch Rules

- Do day-to-day work only on `dev`.
- Do not commit feature, tooling, docs, or security work on `main`.
- Use `main` only for release/tag workflow steps that explicitly require it.
- After any release workflow step that touches `main`, switch back to `dev`.

## Before Staging

- Run `git branch --show-current`.
- If the branch is not `dev`, stop and switch to `dev` unless the user explicitly requested release/tag work.
- Run `git status --short --untracked-files=all`.
- Review the file list and confirm changes are scoped to the current task.
- Preserve unrelated user changes. Do not stage unrelated files unless the user explicitly says to include them.

## Commit Rules

- Commit on `dev` when the current set of changes is coherent and verified enough to preserve.
- Prefer small, scoped commits:
  - `docs: ...`
  - `build: ...`
  - `test: ...`
  - `fix: ...`
  - `chore: ...`
- Do not include generated caches, test output, or release ZIPs.
- If signing fails because the local signer is unavailable in the agent environment, retry with signing disabled for that commit and mention it in the final summary.

## Push Rules

- Push only `dev` during normal work: `git push origin dev`.
- Do not push `main` or tags unless the user has explicitly approved a release.
- After a successful push from the Codex desktop app, emit the git push directive for `dev`.

## Release Exception

For releases, use `.codex/skills/release.md`. The release flow may fast-forward `main` and push a tag, but the repo must end back on `dev`.
