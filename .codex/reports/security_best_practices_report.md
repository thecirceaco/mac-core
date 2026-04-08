# MAC Core Security Best Practices Report

Date: 2026-04-08
Branch reviewed: `dev`

## Scope

Reviewed MAC Core plugin source, bundled SureCart WordPress SDK runtime files, release packaging, GitHub release workflow, and repo-local agent context. This pass focused on WordPress security controls: authentication and authorization, CSRF, XSS, update supply chain, file/database mutation, remote requests, and operational patching.

The `security-best-practices` skill did not include PHP or WordPress-specific reference material, so this report uses repo-grounded review plus official WordPress and GitHub security documentation.

## Executive Summary

No obvious unauthenticated public endpoint, SQL injection, arbitrary file write, arbitrary file include, or direct RCE path was found in MAC Core-owned code.

One concrete XSS hardening change was made during the review because clients may have accounts that can call MAC Core helper functions from templates:

- `src/Utils/GetPostTerms.php` now escapes plain-format term values and separators before returning them.
- `src/Utils/FormatDatetime.php` now escapes plain diff output and all plain date/time output parts before returning them.

Remaining items are mostly defense-in-depth and supply-chain controls around licensed updates, the vendored SDK admin form handler, and the release workflow.

## Findings

### 1. Fixed: Plain helper output could be unsafe in template contexts

Severity before fix: Medium
Status: Fixed in working tree

Client or compromised builder/template access can call global helpers such as `mac_get_post_terms_plain()` and `mac_format_datetime()`. Before this pass, plain-format helpers returned raw term values, custom separators, date labels, and timezone-like values. Those values can be influenced by content/admin inputs and then rendered by builder templates.

Changes made:

- `src/Utils/GetPostTerms.php:79` escapes plain term values.
- `src/Utils/GetPostTerms.php:110` escapes the plain separator.
- `src/Utils/FormatDatetime.php:295` escapes plain lifecycle/diff text.
- `src/Utils/FormatDatetime.php:969` escapes all plain output parts before joining.

This follows WordPress' guidance to escape output late and use context-appropriate escaping.

### 2. Vendored SDK license form should explicitly check capability on submit

Severity: Low to Medium
Status: Open, recommended vendor patch or upstream issue

The SureCart SDK registers the settings page with `manage_options` capability in `licensing/src/Settings.php:55` and passes that capability to the WordPress menu APIs at `licensing/src/Settings.php:120`, `137`, and `152`. The form handler runs from the page callback at `licensing/src/Settings.php:209`.

The handler verifies a nonce at `licensing/src/Settings.php:326` and sanitizes activation/deactivation inputs at `licensing/src/Settings.php:333` and `349`, but `license_form_submit()` does not perform its own explicit `current_user_can( $this->menu_args['capability'] )` check. WordPress states that nonces should not be used for authorization or access control.

Practical exploitability looks limited because the page callback itself is behind `manage_options`, and the nonce is generated on that page. Still, this is a sensitive form because it activates/deactivates the license and stores license material in options. Recommended fix: keep vendored files unchanged unless applying a deliberate vendor patch, but either submit an upstream issue/PR or document a local vendor patch that adds a capability guard before processing `$_POST`.

### 3. Licensed updates make SureCart and release ZIP integrity part of the trust boundary

Severity: Medium
Status: Open operational control

The licensing flow uses the public token in `inc/constants.php:21`, sends requests to SureCart in `licensing/src/Client.php:286`, asks SureCart for the current release in `licensing/src/License.php:120`, and passes the returned package URL to WordPress update data in `licensing/src/Updater.php:152`.

This is expected behavior for licensed updates. The security implication is that a compromised SureCart store account, compromised GitHub release asset, or wrong ZIP upload could distribute malicious plugin code to licensed sites when an admin runs the update.

Recommended controls:

- Enable 2FA on both the SureCart web app account and the `circea.co` WordPress admin account if that account can manage products/releases.
- Keep SureCart store admins minimal.
- Only upload the GitHub Actions ZIP generated from the intended release tag.
- Verify the tag, workflow run, and release asset before uploading to SureCart.
- Add a checksum or release provenance note for each SureCart-uploaded ZIP.
- Prefer signed tags or GitHub artifact attestations if this distribution path grows.

### 4. Release workflow uses tag-pinned third-party actions, not immutable SHAs

Severity: Low to Medium
Status: Open hardening

`.github/workflows/release.yml` uses:

- `actions/checkout@v4` at line 16
- `softprops/action-gh-release@v2` at line 53

GitHub's secure-use guidance says pinning actions to a full-length commit SHA is the only way to use an action as an immutable release. Tags are common, but they can move if an action maintainer account or repository is compromised.

Recommended fix: pin both third-party actions to verified full-length commit SHAs and periodically update them intentionally.

### 5. Automatic updates are intentionally disabled

Severity: Informational to Medium, depending on patch process
Status: Accepted behavior, document process

`src/Services/Core/DisableAutoUpdates.php:24` disables the automatic updater subsystem, and lines `30-32` disable automatic core, plugin, and theme updates.

This means WordPress background auto-updates are off. It does not prevent manual updates in the admin dashboard, nor does it prevent ZIP uploads. For MAC Core specifically:

- With an active SureCart license, the SDK can expose update availability in the WordPress dashboard. An admin still has to manually click update because plugin auto-updates are disabled.
- Without an active license, the SureCart-powered dashboard update path should not provide the protected package. The site can still be updated by manually uploading a ZIP through WordPress.

Recommended control: keep this behavior only if there is a clear manual patching process and release notification path for security fixes.

### 6. Security regression tests are missing

Severity: Low
Status: Already planned in `.codex/todos/mac-core-alignment.md`

There is no Composer/PHPUnit harness yet. Add tests for:

- Plain helper escaping in `GetPostTerms` and `FormatDatetime`.
- Licensing service behavior with missing token and configured token.
- Capability/nonce behavior if a local SDK patch is applied.
- Release metadata/version consistency for `release.json`, `readme.txt`, `mac-core.php`, and `inc/constants.php`.

## Positive Observations

- `mac-core.php` has an `ABSPATH` guard and remains a minimal bootstrap.
- No custom REST routes, AJAX actions, direct SQL, arbitrary file writes, or direct `eval`/`unserialize`/`base64_decode` paths were found.
- Plugin-owned admin notice output in `src/Services/Licensing.php` checks `manage_options` and escapes the message.
- HTML-format helper output uses `esc_html`, `esc_attr`, and `esc_url` in the reviewed paths.
- The SureCart public token is a public token, not a secret API token.
- `release.json` and `licensing/` are included in release ZIPs while dev-only files are excluded.

## Verification Performed

- Confirmed current branch is `dev`.
- Confirmed the initial working tree was clean.
- Searched for superglobals, remote requests, update option calls, transients, nonce usage, capability checks, REST/AJAX hooks, and dangerous PHP primitives.
- Reviewed MAC Core services, utilities, bootstrap/autoload, bundled SureCart SDK runtime files, release workflow, `.gitattributes`, and `.gitignore`.
- Ran `php -l` across all PHP files before code changes.
- Ran `php -l` for the two modified helper files after the XSS hardening change.

## Sources

- [WordPress Nonces](https://developer.wordpress.org/apis/security/nonces/)
- [WordPress current_user_can()](https://developer.wordpress.org/reference/functions/current_user_can/)
- [WordPress Escaping Data](https://developer.wordpress.org/apis/security/escaping/)
- [WordPress Sanitizing Data](https://developer.wordpress.org/apis/security/sanitizing/)
- [GitHub Actions Secure Use](https://docs.github.com/en/actions/reference/security/secure-use)
- [SureCart WordPress SDK](https://github.com/surecart/wordpress-sdk)
