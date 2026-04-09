# MAC Core Codex Context

This directory stores repo-local context for agents working on MAC Core. Keep detailed plans, todos, reusable skills, and durable working notes here instead of expanding `AGENTS.md` with implementation backlog.

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
- `todos/mac-core-alignment.md`: Actionable implementation checklist and verification steps for the alignment pass.
- `todos/surecart-licensing.md`: SureCart licensing implementation and verification checklist.
- `skills/wordpress-plugin-oop/SKILL.md`: MAC Core-specific implementation discipline for adding WordPress behavior through the plugin service architecture.
- `skills/surecart-licensing/SKILL.md`: MAC Core-specific rules for maintaining the SureCart licensing integration.
- `skills/dev-zip-testing/SKILL.md`: Local test ZIP workflow for building a plugin package from the current `dev` commit before a real release.
- `skills/release/SKILL.md`: MAC Core release process, version checks, and branch discipline.
- `skills/dev-branch-git/SKILL.md`: Add/commit/push guardrails for keeping normal work on `dev`.
- `reports/security_best_practices_report.md`: Security best-practices review findings for MAC Core.
- `reports/mac-core-threat-model.md`: Repo-grounded threat model for MAC Core.
- `reports/security-ownership-summary.md`: Ownership-map summary and accepted single-maintainer constraint notes.
- `reports/ownership-map-out/`: Raw ownership-map export files generated during the security audit.

## Usage

Read `AGENTS.md` first for always-needed repo guardrails, then read the relevant files in this directory before making implementation changes.

For local plugin testing on `dev`, use the dev ZIP helper skill and build a test ZIP from the current commit with `pwsh -File .\bin\build-dev-zip.ps1`. The helper keeps `dist/` self-cleaning by replacing older `mac-core-dev-*.zip` files, and it can target Downloads with `-OutputDir "$env:USERPROFILE\Downloads"` if explicitly requested. When a feature set is ready for QA, mention this helper before suggesting a real release.
