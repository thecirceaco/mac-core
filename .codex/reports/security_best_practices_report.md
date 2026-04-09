# MAC Core Security Best Practices Report

Date: 2026-04-09
Branch reviewed: `dev`

## Scope

This pass reviewed MAC Core-owned runtime code, admin/settings flows, uninstall behavior, release workflows, and release-integrity controls. The vendored SureCart SDK under `inc/Vendor/SureCart/Licensing/` was reviewed only as inherited dependency surface. Its internals are accepted as upstream-owned and are not part of the local remediation scope unless the integration itself breaks.

## Executive Summary

No obvious critical issue was found in MAC Core-owned code. The main actionable finding from this audit was supply-chain hardening in GitHub Actions, and that has been fixed by pinning third-party actions to immutable commit SHAs.

I did not find plugin-owned unauthenticated endpoints, custom AJAX/REST handlers, arbitrary file-write paths, dynamic code execution, or known unsafe plain-output regressions in the reviewed code. The remaining meaningful risks are accepted or inherited:

- `inc/Vendor/SureCart/Licensing/` remains an inherited third-party trust boundary from SureCart.
- The project currently has one maintainer, which is an accepted operational constraint.

## Findings

### 1. Fixed: GitHub Actions were pinned to floating tags instead of immutable SHAs

Severity before fix: Medium
Status: Fixed in this audit

Before this pass, the release and quality workflows used floating tags such as `actions/checkout@v4`, `shivammathur/setup-php@v2`, and `softprops/action-gh-release@v2`. For this repo, that matters because the GitHub workflow produces the ZIP later uploaded to SureCart and distributed to licensed sites.

Fixed in:

- [release.yml](D:/business/projects/mac-core/.github/workflows/release.yml)
- [quality.yml](D:/business/projects/mac-core/.github/workflows/quality.yml)

Pinned actions:

- `actions/checkout@93cb6efe18208431cddfb8368fd83d5badbf9bfd` (`v5.0.1`)
- `shivammathur/setup-php@accd6127cb78bee3e8082180cb391013d204ef9f` (`2.37.0`)
- `softprops/action-gh-release@153bb8e04406b158c6c84fc1615b65b24149a1fe` (`v2.6.1`)

Residual note: pinning is not a one-time action. These SHAs still need deliberate refreshes during normal maintenance.

### 2. Accepted inherited dependency surface: SureCart SDK internals

Severity: Informational
Status: Accepted

The vendored SureCart SDK in `inc/Vendor/SureCart/Licensing/` remains part of the runtime trust boundary because it handles license state and update metadata. It is not treated as a local remediation target in this audit because those files are upstream SDK internals and you explicitly do not want to carry local forks there.

This means:

- the integration points in [LicensingService.php](D:/business/projects/mac-core/src/Licensing/LicensingService.php) remain in scope
- the vendored SDK internals are documented as inherited surface
- any issue there should normally be handled by upstream monitoring, version bumps, or a deliberate vendor override only if necessary

### 3. Accepted operational constraint: single maintainer / bus factor 1

Severity: Informational
Status: Accepted

The ownership map shows one contributor across the full repo:

- `people: 1`
- `files: 124`
- `commits: 85`

Source: [summary.json](D:/business/projects/mac-core/.codex/reports/ownership-map-out/summary.json)

This is a real release and continuity risk, but not a code defect. It is documented as an accepted constraint for now.

## Positive Security Posture

- [SettingsController.php](D:/business/projects/mac-core/src/Settings/SettingsController.php) enforces both `manage_options` and a nonce before saving settings.
- [AdminPage.php](D:/business/projects/mac-core/src/Admin/AdminPage.php) registers the top-level admin UI behind `manage_options`.
- [FormatDatetime.php](D:/business/projects/mac-core/src/Utils/FormatDatetime.php) escapes plain output parts before returning them.
- [GetPostTerms.php](D:/business/projects/mac-core/src/Utils/GetPostTerms.php) escapes plain output values and separators.
- [uninstall.php](D:/business/projects/mac-core/uninstall.php) only deletes local data when the explicit opt-in setting is enabled.
- Repo-wide grep did not find plugin-owned `register_rest_route`, `wp_ajax_`, `admin_post_`, `eval`, `unserialize`, shell execution, or upload-file handlers in MAC Core-owned code.

## Verification Performed

- Reviewed:
  - [AdminPage.php](D:/business/projects/mac-core/src/Admin/AdminPage.php)
  - [SettingsController.php](D:/business/projects/mac-core/src/Settings/SettingsController.php)
  - [LicensingService.php](D:/business/projects/mac-core/src/Licensing/LicensingService.php)
  - [DisableAdminBar.php](D:/business/projects/mac-core/src/Policies/Core/DisableAdminBar.php)
  - [uninstall.php](D:/business/projects/mac-core/uninstall.php)
  - [release.yml](D:/business/projects/mac-core/.github/workflows/release.yml)
  - [quality.yml](D:/business/projects/mac-core/.github/workflows/quality.yml)
- Ran repo-wide searches for:
  - public entry points
  - dangerous PHP primitives
  - remote requests
  - option/transient mutation
  - update hooks
- Generated ownership artifacts under [ownership-map-out](D:/business/projects/mac-core/.codex/reports/ownership-map-out)

## Recommended Follow-Up

1. Keep workflow SHA pins current during normal release maintenance.
2. Re-run this audit when MAC Core adds:
   - public REST/AJAX endpoints
   - add-on loading
   - custom tables or migrations
   - new third-party runtime dependencies
3. Track SureCart SDK updates deliberately and review upstream changelogs before bumping the vendored copy.

## Sources

- [GitHub Actions secure use reference](https://docs.github.com/en/actions/reference/security/secure-use)
- [actions/checkout v5.0.1 commit](https://github.com/actions/checkout/commit/93cb6efe18208431cddfb8368fd83d5badbf9bfd)
- [shivammathur/setup-php 2.37.0 commit](https://github.com/shivammathur/setup-php/commit/accd6127cb78bee3e8082180cb391013d204ef9f)
- [softprops/action-gh-release v2.6.1 commit](https://github.com/softprops/action-gh-release/commit/153bb8e04406b158c6c84fc1615b65b24149a1fe)
