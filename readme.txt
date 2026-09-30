=== MAC Core ===
Contributors: thecirceaco
Tags: core, agency
Requires at least: 6.9
Tested up to: 7.1.2
Requires PHP: 8.3
Stable tag: 1.3.2
License: GPL v3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Company standard core functionality plugin for WordPress projects built by Circea.

== Description ==

MAC Core provides shared, standardized functionality used across Circea WordPress projects.
It is intended for internal and client use and is not a general-purpose plugin.

Features may evolve over time and are tailored to the agency’s development standards.

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory, or install via ZIP.
2. Activate the plugin through the Plugins screen.

== Changelog ==

= 1.3.2 =
* Activating or deactivating the license now brings you back to the License tab instead of the Settings tab. The bundled SureCart SDK lost the tab when it redirected after the license form.

= 1.3.1 =
* The Updates screen and "View details" no longer say "Not tested" for a MAC Core update on a WordPress patch release. A tested version counts for its whole branch, so 7.1 covers 7.1.2 and 7.1.3, as for plugins on wordpress.org.
* Tested up to WordPress 7.1.2.

= 1.3.0 =
* MAC Core's page now sits under Settings > MAC Core. A new `Top-level admin menu` setting, off by default, gives it its own item in the main admin menu with the MAC icon, as before. After updating, the page moves under Settings on every site until that setting is turned on.
* The settings page has new texts: a short title for each row, the full sentence next to each checkbox and a short description below it where it helps. The sections are now General, Media and Helpers, the Uninstall group joined Plugin, and Content Types and Content became one Content group. Stored settings don't change.
* Saving sends you back to the tab you saved, so reloading the page no longer sends the form again, and each notice shows once. A save that moves the page takes you to its new address.
* `mac_core_get_post_type_label()` and `mac_core_get_taxonomy_label()` escape their output for HTML, like the other text helpers, because Bricks prints `{echo:}` results as they are.
* `mac_core_count_array_items()`, `mac_core_format_price()` and `mac_core_get_plugin_status()` accept the string arguments a Bricks `{echo:}` tag passes, and missing ones. A wrong argument returns 0, an empty string or false instead of an error.
* The bundled SureCart SDK reads names prefixed for MAC Core: `MAC_CORE_SURECART_LICENSING_ENDPOINT`, `mac_core_surecart_licensing_endpoint`, `mac_core_surecart_client_license_form_action` and `mac_core_surecart_licensing_is_local`. Values set for another plugin's copy of the SDK no longer change MAC Core's licensing. A site that set the old names for MAC Core needs the new ones.
* Add-on settings: a checkbox field can set `option`, the sentence next to the box; its `description` then shows below the box.

= 1.2.0 =
* Comment settings now only close comments: they never reopen comments closed on a post or page, or closed by WordPress on older posts. Before, they forced comments and pings open.
* `mac_core_format_price()` escapes its plain output for HTML, accepts only three-letter currency codes (others fall back to USD), caps `decimals` at 10 and ignores separators longer than 8 bytes.
* MAC Core now starts at the beginning of `plugins_loaded`, so add-ons that load after it keep their stored settings. Call `mac_core_*` helpers from hooks or templates, not directly from another plugin's main file.
* Add-on settings gain a `secret` field type that is never shown again in the form, a `sanitize_callback` option and size limits for list fields.
* The descriptions for automatic updates, native Posts, Site Health, the last login column and video uploads now say exactly what each setting covers. Two labels changed: `Hide native Posts in admin` and `Hide Site Health`.
* The License tab requires `manage_options`, and PHP files return nothing when opened directly.
* `Update URI` points to a Circea host, so no other updater plugin can supply MAC Core. Updates still come from SureCart.
* If a site relied on comments being forced open, reopen discussion on the posts that need it after updating.

= 1.1.2 =
* Updated the bundled SureCart licensing SDK to v1.2.1. The license key and activation now stay stored when SureCart can't be reached or returns an error, so the site keeps receiving MAC Core updates. Before, any API error removed them until the key was entered again.
* Documented the bundled SDK and its two local changes in `inc/Vendor/SureCart/Licensing/README.md`.
* Releases are now verified and built by GitHub before publishing: the tag must be on `main`, with matching versions and passing tests, and each release ships a SHA-256 checksum and a build provenance attestation.

= 1.1.1 =
* Fixed saving one admin tab (`Settings` or `Helpers`) switching off every checkbox on the other tab. Each tab now saves only its own settings, and the other tab keeps its stored values.
* Added regression tests that save each tab and check that the other tab keeps its values.
* Settings switched off by the earlier behavior aren't restored automatically, so re-check both tabs after updating.

= 1.1.0 =
* Updated `mac_core_format_datetime()` so the built-in `event` preset prefers `event_start` and `event_end` while preserving `event_start_datetime` and `event_end_datetime` as fallback aliases.
* Added IANA timezone handling for `event_timezone`, including fallback to the WordPress site timezone when event timezone data is missing, blank, or invalid.
* Added timezone-aware machine datetime output for `attr` and HTML `<time datetime="">` values, using PHP `c` for time-bearing values and `Y-m-d` for date-only values.
* Added ACF choice array label/value handling for timezone display in timezone-enabled views.
* Added unit coverage for alias precedence, timezone fallback, date-only attributes, and timezone-aware HTML datetime attributes.

= 1.0.0 =
* Marked `1.0.0` as the first stable baseline for the current MAC Core feature set.
* Documented the supported public helper API around `mac_core_format_datetime()`, `mac_core_format_price()`, `mac_core_count_array_items()`, `mac_core_get_post_type_label()`, `mac_core_get_taxonomy_label()`, `mac_core_get_post_terms()`, `mac_core_get_plugin_status()`, and `mac_core_get_theme_status()`.
* Treated the documented `mac_core_*` hooks and `mac_core_format_datetime_*` filters as the supported extension surface for add-ons and site-specific overrides.
* Split utility controls into a dedicated `Helpers` admin tab while keeping the existing `mac_core_settings['utils']` storage and upgrade path intact.
* Clarified helper gating and frontend admin-bar setting copy, and kept multisite outside the supported scope for `1.0.0`.
* Required no settings schema migration when upgrading from the current `0.x` line.

= 0.8.0 =
* Added a default-off `Utils` settings module so utility wrappers are only available when explicitly enabled.
* Unified the public utility API around wrapper-first helpers including `mac_core_format_datetime()`, `mac_core_format_price()`, `mac_core_count_array_items()`, `mac_core_get_post_type_label()`, `mac_core_get_taxonomy_label()`, `mac_core_get_post_terms()`, `mac_core_get_plugin_status()`, and `mac_core_get_theme_status()`.
* Added the new generic `FormatPrice` utility helper.
* Removed the older convenience wrappers in favor of the unified helper surface.
* Added migration-focused coverage around the default-off utils loader and wrapper availability.

= 0.7.0 =
* Added a new `Control comments in MAC Core` master setting so MAC Core comment behavior is explicitly opt-in.
* Changed the comment policy to be a true no-op unless that new master setting is enabled, including comment-related admin UI.
* Added test coverage for the new passive and active comment-control paths.

= 0.6.7 =
* Updated the default developer branding company and URL settings from All Phase Media to Circea.

= 0.6.6 =
* Added filter-based `FormatDatetime` extension points for config, presets, and views so child themes or site plugins can extend formatting without editing MAC Core.
* Added test coverage for custom presets, custom views, filtered defaults, and invalid filter fallback behavior.

= 0.6.5 =
* Removed the duplicate custom `View details` plugin row link and let WordPress keep the built-in details modal link.
* Reordered the plugin row links to `View details | Support | Documentation` and renamed `Docs` to `Documentation`.
* Marked the plugin as tested up to WordPress `7.0`.

= 0.6.4 =
* Added installed-plugin screen links for `Settings`, `License`, and right-side `View details`, `Docs`, and `Support`.
* Wired the plugin row `View details` link to the standard WordPress plugin information modal.
* Added unit coverage for the new plugin listing links and bootstrap registration.

= 0.6.3 =
* Pinned the GitHub Actions release and quality workflows to immutable action SHAs.
* Moved the bundled SureCart runtime into `inc/Vendor/SureCart/Licensing` and removed unused upstream package metadata files.
* Updated release packaging rules so vendored runtime files stay in the ZIP while root Composer `vendor/` stays excluded.

= 0.6.2 =
* Cleaned up the settings model by removing unused media inputs and keeping fixed upload policy defaults where customization was unnecessary.
* Added explicit excerpt, admin bar target, and uninstall cleanup settings for clearer behavior and easier site maintenance.
* Improved settings copy and textareas for image width and removed-size inputs, and added uninstall cleanup coverage in the test suite.

= 0.6.1 =
* Grouped related settings more clearly on the Settings page, including the comments controls.
* Switched removed image sizes to a textarea for easier editing of long lists.
* Improved CSV-style settings parsing so comma-separated and line-separated values both work.

= 0.6.0 =
* Added the modular MAC Core admin experience with Settings, License, and Support views.
* Added repository-backed Core and Media policy settings for comments, media behavior, branding, dashboard cleanup, and related defaults.
* Refactored the plugin into Admin, Licensing, Settings, and Policy modules for cleaner long-term maintenance.

= 0.5.4 =
* Switched the MAC Core admin menu icon to the fill logomark and updated the license page slug to `mac-core`.
* Isolated the bundled SureCart SDK under the `MacCore\\Vendor\\SureCart\\Licensing` namespace to avoid plugin collisions.
* Added SHA-256 checksum and provenance assets to GitHub release artifacts.

= 0.5.3 =
* Refreshed the MAC logomark SVG assets used by the plugin.

= 0.5.2 =
* Replaced the default MAC Core license menu cog with the outline MAC logomark.

= 0.5.1 =
* Added Composer-based coding standards and PHPUnit tooling.
* Added PHPUnit coverage for SureCart licensing behavior and release metadata sync.
* Hardened helper output escaping and core capability/timestamp handling.

= 0.5.0 =
* Added SureCart licensing support for licensed manual updates.
* Added MAC Core license management page in WordPress admin.
* Added release metadata for SureCart-powered plugin distribution.
* Added shared date/time formatting utility.
* Updated service and utility organization for the MAC Core OOP plugin architecture.
* Updated release packaging rules for dev-only files and bundled licensing assets.

= 0.4.3 =
* Updated .gitattributes and normalized.

= 0.4.2 =
* Updated version number.

= 0.4.1 =
* Updated README.md.

= 0.4.0 =
* Added release workflow.

= 0.3.0 =
* Updated README.md

= 0.2.0 =
* Fixed Kernel autoload path resolution
* Normalized plugin bootstrap
* Improved image size management
* Internal cleanup and stability fixes

= 0.1.0 =
* Initial development release.
