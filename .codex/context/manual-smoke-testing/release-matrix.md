# MAC Core Release Test Matrix

Use this matrix when deciding what must be tested before shipping a `MAC Core` release.

The release type sets the minimum bar. If a lower-version release touches a higher-risk area, test to the higher-risk bar.

## Core Principle

- Do not treat version numbers as an excuse to under-test.
- If a patch touches licensing, updates, settings persistence, helper output, builder behavior, install/upgrade flow, or packaging, it needs human QA even if the semantic version is small.
- If a release changes public behavior, defaults, extension seams, or upgrade expectations, tell the user explicitly that human testing is required and point them to the exact `.codex` assets to use.

## Shared Automated Baseline

Run these for every shipped release unless the release is purely `.codex` or docs-only and no plugin artifact will be produced:

- Full PHPUnit.
- PHP syntax checks for changed PHP files when relevant.
- `release.json` parse validation.
- Version-carrier consistency checks.
- Release archive contents/exclusion check.
- Delete generated local artifacts from `dist/` before the real release/tag flow, even though `dist/` is gitignored and export-ignored.
- AI context review:
  - `D:\business\projects\mac-core\.codex\skills\ai-context-sync\SKILL.md`

## Patch Release

Examples:

- isolated bug fix
- copy-only admin tweak
- low-risk internal refactor with no intended behavior change
- metadata-only release follow-up

### Human QA Requirement

Human QA is required when the patch touches any shipped runtime behavior, including:

- admin UI or settings save behavior
- helper output or helper gating
- child-theme extension seams or supported hooks
- licensing or update flow
- install, activation, upgrade, or packaging behavior
- anything the user will actually click, see, or rely on in production

Human QA is not required for:

- `.codex`-only changes
- docs-only changes
- release-note-only changes
- metadata-only changes with no new plugin ZIP needed

### Minimum Human Scope When Required

- Verify only the changed area plus a short regression pass around it.
- If helpers, `FormatDatetime`, or builder-facing behavior changed:
  - use `runbook.md`
  - import `acf-import.json` if the test site does not already have the smoke fixture
- If settings/admin changed:
  - run the admin/settings part of `runbook.md`, not just the Bricks snippet
  - load the relevant admin page
  - save the affected settings
  - confirm persistence after refresh
- If licensing/update changed:
  - load the license screen
  - smoke the intended activation/update path

## Minor Release

Examples:

- new setting or toggle
- new helper wrapper
- new supported hook/filter
- new admin tab or admin IA change
- backward-compatible behavior expansion

### Human QA Requirement

Human QA is always required.

### Minimum Human Scope

- Fresh single-site install.
- In-place upgrade from the latest released version in the current major line.
- Targeted regression of the changed feature set.
- If helpers, `FormatDatetime`, or builder-facing behavior changed:
  - use the full smoke-test package:
    - `D:\business\projects\mac-core\.codex\context\manual-smoke-testing\runbook.md`
    - `D:\business\projects\mac-core\.codex\context\manual-smoke-testing\acf-import.json`
- If settings or admin IA changed:
  - run the admin/settings part of `runbook.md`, not just the Bricks snippet
  - verify tab routing
  - verify save behavior
  - verify stored values survive refresh
- If docs/support copy changed in a user-facing flow:
  - confirm the rendered copy matches intent

## Major Release

Examples:

- breaking change
- public contract change
- settings schema/default change with upgrade impact
- major admin IA shift
- licensing/update/packaging model change
- first stable release that freezes public expectations

### Human QA Requirement

Human QA is always required and must be broader than a minor release.

### Minimum Human Scope

- Fresh single-site install.
- In-place upgrade from the latest released version you expect users to come from.
- Full regression of all changed surfaces.
- Full smoke-test package if helpers, builder behavior, or `FormatDatetime` are in scope:
  - `D:\business\projects\mac-core\.codex\context\manual-smoke-testing\runbook.md`
  - `D:\business\projects\mac-core\.codex\context\manual-smoke-testing\acf-import.json`
- Licensing and update smoke when any release/install/update infrastructure changed.
- Extra upgrade-preservation checks when settings/defaults/storage changed.
- Do not treat the Bricks Home page snippet as a full settings test. It only validates frontend runtime against the saved state.

## How To Use The Smoke-Test Package

When the release requires builder/helper human QA:

1. Build a local QA ZIP from committed `HEAD`:
   - `D:\business\projects\mac-core\.codex\skills\dev-zip-testing\SKILL.md`
2. On the test site, import:
   - `D:\business\projects\mac-core\.codex\context\manual-smoke-testing\acf-import.json`
3. Follow:
   - `D:\business\projects\mac-core\.codex\context\manual-smoke-testing\runbook.md`
4. Run both parts of the runbook:
   - the admin/settings checklist
   - the frontend Bricks helper smoke
5. Add the child-theme `functions.php` snippet from the runbook.
6. Add the Bricks Home page code snippet from the runbook.
7. Run both datetime passes:
   - separate date/time fields populated, combined datetime empty
   - combined datetime populated, separate date/time fields empty
8. If `FormatDatetime` parsing or precedence changed, also run the optional mixed-source pass from the runbook:
   - one side from `*_datetime`
   - the other side from separate date/time fields

## Agent Obligation

Before wrapping a release-prep task, explicitly tell the user:

- which release tier applies: `patch`, `minor`, or `major`
- whether human QA is required
- why that requirement applies
- which `.codex` files to use
- the concrete WordPress testing steps when human QA is required
- whether the Bricks snippet alone is sufficient; if settings/admin changed, explicitly say no

Do not make the user infer the test burden from a vague “should probably test this” closeout.
