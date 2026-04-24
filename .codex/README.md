# MAC Core Codex Context

This directory stores repo-local context for agents working on MAC Core. Keep detailed plans, reusable skills, and durable working notes here instead of expanding `AGENTS.md` with implementation backlog.

## Structure Rules

- Keep repo-local AI docs, durable context notes, plans, reports, and reusable skills under `.codex/`.
- The only intended repo-root AI guidance file is `AGENTS.md`, which points agents into `.codex/`.
- Keep every repo-local skill in the standard Codex structure: `.codex/skills/<skill-name>/SKILL.md`.
- Do not add loose skill markdown files outside `.codex/skills/`.

## Contents

- `plans/mac-core-alignment.md`: Full alignment plan for bringing MAC Core in line with the `mac-bricks` and `mac-etch` baseline while preserving the plugin OOP architecture.
- `plans/surecart-licensing.md`: SureCart licensing implementation plan for activation and licensed manual updates.
- `plans/scalable-plugin-tooling.md`: Composer, PHPUnit, CI, and future module-boundary plan inspired by the Etch review.
- `plans/deferred-architecture-roadmap.md`: Deferred architecture notes for lifecycle, migrations, feature flags, runtime Composer dependencies, and future modules.
- `plans/addons-architecture.md`: Lightweight extension model for future MAC add-ons.
- `plans/mac-core-settings-policy-modules.md`: Admin/settings/policy module plan for the config-backed MAC Core product surface.
- `context/surecart-licensing-research.md`: Research notes from the SureCart licensing docs and `surecart/wordpress-sdk`.
- `context/surecart-sdk-provenance.md`: Source, tag, runtime-file path, and maintenance notes for the vendored SureCart WordPress SDK.
- `context/etch-1.4.9-review.md`: Detailed notes from reviewing the Etch 1.4.9 plugin ZIP and how to adapt the useful patterns.
- `context/acss-4.0.0-rc-1-review.md`: Automatic.css review notes for admin routing, settings storage, lifecycle timing, and extension seams.
- `context/mac-governance-source.md`: Pointer map to the shared governance rules now authored in the sibling `mac` repo.
- `context/mac-docs-sync.md`: Durable map of the main `mac-docs` touchpoints that should be reviewed when `MAC Core` changes.
- `context/manual-smoke-testing/`: Grouped smoke-test package with the WordPress/Bricks runbook, reusable ACF import fixture, and release-type QA matrix for the `galleries` CPT.
- `skills/wordpress-plugin-oop/SKILL.md`: MAC Core-specific implementation discipline for adding WordPress behavior through the plugin service architecture.
- `skills/surecart-licensing/SKILL.md`: MAC Core-specific rules for maintaining the SureCart licensing integration.
- `skills/dev-zip-testing/SKILL.md`: Local test ZIP workflow for building a plugin package from the current `dev` commit before a real release.
- `skills/manual-smoke-testing/SKILL.md`: Workflow for keeping the manual WordPress and Bricks smoke runbook current and using it before release.
- `skills/release/SKILL.md`: MAC Core release process, version checks, and branch discipline.
- `skills/dev-branch-git/SKILL.md`: Add/commit/push guardrails for keeping normal work on `dev`.
- `skills/mac-docs-sync/SKILL.md`: Workflow for reviewing and updating `mac-docs` when `MAC Core` public behavior changes.
- `skills/ai-context-sync/SKILL.md`: Workflow for reviewing and updating repo-local AI context notes and skills when `MAC Core` behavior or release process changes.
- `reports/security_best_practices_report.md`: Security best-practices review findings for MAC Core.
- `reports/mac-core-threat-model.md`: Repo-grounded threat model for MAC Core.
- `reports/security-ownership-summary.md`: Ownership-map summary and accepted single-maintainer constraint notes.
- `reports/ownership-map-out/`: Raw ownership-map export files generated during the security audit.

## Usage

Read `AGENTS.md` first for always-needed repo guardrails, then read the relevant files in this directory before making implementation changes.

For shared governance rules that should not be redefined per product repo, read the sibling source repo first:

- `D:\business\projects\mac\governance\local-codex-model.md`
- `D:\business\projects\mac\governance\docs-and-changelog.md`
- `D:\business\projects\mac\governance\git-and-releases.md`

After plugin changes that affect admin IA, settings/storage behavior, public helpers/hooks, licensing/update flow, or release workflow, use the AI context sync skill to review `.codex` and update stale notes or skills in the same task. Pair it with the MAC Docs sync skill when the change is also public-facing.

For local plugin testing on `dev`, use the dev ZIP helper skill and build a test ZIP from the current commit with `pwsh -File .\bin\build-dev-zip.ps1`. The helper keeps `dist/` self-cleaning by replacing older `mac-core-dev-*.zip` files, and it can target Downloads with `-OutputDir "$env:USERPROFILE\Downloads"` if explicitly requested. When a feature set is ready for QA, mention this helper before suggesting a real release.

When a change affects helpers, `FormatDatetime`, child-theme extension seams, or the builder-facing smoke path, pair the dev ZIP flow with the manual smoke-testing skill and keep `context/manual-smoke-testing/runbook.md` current in the same task.

Before any release, explicitly run the AI context sync review even if no obvious `.codex` file changed during the feature work.
