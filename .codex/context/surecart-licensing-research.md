# SureCart Licensing Research

## Sources

- SureCart licensing setup guide: https://surecart.com/docs/licensing-setup-and-functionality/
- SureCart WordPress SDK repository: https://github.com/surecart/wordpress-sdk
- SDK tag pinned for MAC Core: https://github.com/surecart/wordpress-sdk/tree/v1.1.2
- Runtime SDK files reviewed: `src/Client.php`, `src/License.php`, `src/Activation.php`, `src/Settings.php`, and `src/Updater.php`.

## SureCart Setup Flow

- SureCart licensing requires the WordPress SDK inside the plugin or theme being licensed.
- The current SDK README says to load and initialize the SDK on the WordPress `init` hook. The pinned `v1.1.2` README has older example wording that shows direct initialization in the main plugin file; MAC Core follows the current README plus the repo architecture guardrail by initializing from a service on `init`.
- The plugin release ZIP should contain the plugin files, the vendored SDK runtime files, and a root `release.json`.
- In SureCart, create or edit the product, add the plugin ZIP as a secure storage download, enable license creation, choose the allowed activation count, and set the uploaded ZIP as the current release.
- Customers download the ZIP and copy the license key from their SureCart customer dashboard.
- On the client WordPress site, the customer uploads and activates the plugin, opens the plugin's license settings page, enters the license key, and activates the site.
- The SureCart merchant can inspect license usage in SureCart's Licenses area.
- For updates, bump the plugin header version and the `release.json` version, package and upload the new ZIP, set it as the current release, then WordPress can show an update to licensed installations.

## `release.json`

- The SDK README says `release.json` should live at the root of the plugin or theme project.
- Required fields include `name`, `slug`, `author`, `author_profile`, `version`, `requires`, `tested`, `requires_php`, and `sections`.
- `sections` should include at least a `changelog` HTML string; description and FAQ sections can also be present.
- The `slug` must match the installed plugin folder name. For MAC Core this must be `mac-core`.
- MAC Core's `release.json` must be kept synchronized with `mac-core.php`, `MAC_CORE_VERSION`, and `readme.txt`.

## SDK Behavior

- `SureCart\Licensing\Client` derives the plugin basename, slug, version, and type from the main plugin file passed to the constructor.
- `Client` initializes `License`, `Activation`, and `Updater`; settings are initialized when `settings()` is called.
- `Client::send_request()` calls the SureCart API endpoint, defaulting to `https://api.surecart.com`, and sends the public token as a bearer token when configured.
- `License::activate()` validates the license, creates an activation, stores the activation ID through `Settings`, and validates that the current release slug matches the client slug.
- `Activation::create()` sends the site URL as the activation fingerprint and the site name as the activation name.
- `Settings::add_page()` registers the SDK's built-in license form as a `menu`, `submenu`, or `options` page.
- `Updater` hooks into `pre_set_site_transient_update_plugins` and `plugins_api` for plugins. It exposes licensed updates by retrieving the current release from SureCart and caching version information for three hours.

## MAC Core Decisions

- Bundle the SDK runtime from GitHub tag `v1.1.2` under `inc/Vendor/SureCart/Licensing/`.
- Use the SDK's built-in license page as a top-level admin menu.
- Menu label: `MAC Core`.
- Page title: `MAC Core License`.
- Menu slug: `mac-core`.
- Capability: `manage_options`.
- Textdomain: `mac-core`.
- Public token source: `MAC_CORE_SURECART_PUBLIC_TOKEN`, passed through the `mac_core_surecart_public_token` filter.
- If the public token is blank, MAC Core should skip SDK initialization and show an admin-only configuration notice.
- Licensing should control activation and licensed manual update availability only. Existing MAC Core services must still load without an active license.
- Keep the existing `DisableAutoUpdates` policy unchanged; it disables automatic updates globally, while SureCart can still expose manual update availability through WordPress update checks.

## SDK Provenance

- The bundled SDK runtime is from `surecart/wordpress-sdk` tag `v1.1.2`.
- The tag's `composer.json` declares license `MIT`.
- MAC Core keeps provenance details in `.codex/context/surecart-sdk-provenance.md` and only ships the five runtime PHP files, not upstream package metadata files.
