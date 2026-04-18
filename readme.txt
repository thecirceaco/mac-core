=== MAC Core ===
Contributors: thecirceaco
Tags: core, agency
Requires at least: 6.9
Tested up to: 7.0
Requires PHP: 8.3
Stable tag: 0.8.0
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

= 0.8.0 =
* Added a default-off `Utils` settings module so utility wrappers are only available when explicitly enabled.
* Unified the public utility API around wrapper-first helpers including `mac_format_datetime()`, `mac_format_price()`, `mac_count_array_items()`, `mac_get_post_type_label()`, `mac_get_taxonomy_label()`, `mac_get_post_terms()`, `mac_get_plugin_status()`, and `mac_get_theme_status()`.
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
