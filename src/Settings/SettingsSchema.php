<?php
/**
 * Settings schema and defaults.
 *
 * @package mac-core
 */

declare(strict_types=1);

namespace MacCore\Settings;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

final class SettingsSchema
{
	/**
	 * Default `max_items` for list fields.
	 */
	public const DEFAULT_MAX_ITEMS = 500;

	/**
	 * Default `max_item_length` for list fields, in characters.
	 */
	public const DEFAULT_MAX_ITEM_LENGTH = 200;

	/**
	 * Return all settings sections.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function get_sections(): array
	{
		$sections = [
			'core'  => [
				'title'       => 'Core Policies',
				'description' => 'Site-wide WordPress behavior for admin, editorial, and branding defaults.',
				'fields'      => [
					'top_level_menu'                   => [
						'type'        => 'checkbox',
						'group'       => 'Plugin',
						'label'       => 'Top-level admin menu',
						'description' => 'Show MAC Core as a top-level admin menu item',
						'default'     => false,
					],
					'delete_data_on_uninstall'         => [
						'type'        => 'checkbox',
						'group'       => 'Uninstall',
						'label'       => 'Delete plugin data on uninstall',
						'description' => 'Remove all plugin data when the plugin is deleted from the site.',
						'default'     => false,
					],
					'developer_branding_enabled'       => [
						'type'        => 'checkbox',
						'group'       => 'Branding',
						'label'       => 'Output developer branding',
						'description' => 'Adds the frontend HTML comment and replaces the admin footer text.',
						'default'     => false,
					],
					'developer_branding_author'        => [
						'type'        => 'text',
						'group'       => 'Branding',
						'label'       => 'Branding author',
						'description' => 'Shown in the frontend source comment and admin footer.',
						'default'     => 'Mihai Circea',
					],
					'developer_branding_company'       => [
						'type'        => 'text',
						'group'       => 'Branding',
						'label'       => 'Branding company',
						'description' => 'Used in the frontend source comment and admin footer link text.',
						'default'     => 'Circea',
					],
					'developer_branding_url'           => [
						'type'        => 'url',
						'group'       => 'Branding',
						'label'       => 'Branding URL',
						'description' => 'Used for the admin footer link and frontend source comment.',
						'default'     => 'https://circea.co',
					],
					'comment_control_enabled'          => [
						'type'        => 'checkbox',
						'group'       => 'Comments',
						'label'       => 'Control comments in MAC Core',
						'description' => 'When disabled, MAC Core leaves frontend comment behavior and comment-related admin UI untouched.',
						'default'     => false,
					],
					'comments_enabled'                 => [
						'type'        => 'checkbox',
						'group'       => 'Comments',
						'label'       => 'Allow comments globally',
						'description' => 'When off, comments and pings are closed on every post type, and MAC Core hides the Comments menu and toolbar item, redirects the Comments screen to the Dashboard, and hides existing comments in themes that use the classic comments template. Comments are not deleted: block themes, the dashboard Activity widget and the REST API can still show them. When on, MAC Core only limits comments: each post keeps its own discussion settings, and WordPress still closes comments on old posts.',
						'default'     => true,
					],
					'comments_posts_enabled'           => [
						'type'        => 'checkbox',
						'group'       => 'Comments',
						'label'       => 'Allow comments on posts',
						'description' => 'Only applies when comments are allowed globally. Never reopens comments closed on a post.',
						'default'     => true,
					],
					'comments_pages_enabled'           => [
						'type'        => 'checkbox',
						'group'       => 'Comments',
						'label'       => 'Allow comments on pages',
						'description' => 'Only applies when comments are allowed globally. Never reopens comments closed on a page.',
						'default'     => true,
					],
					'disable_native_posts'            => [
						'type'        => 'checkbox',
						'group'       => 'Content Types',
						'label'       => 'Hide native Posts in admin',
						'description' => 'Hides native Posts in the admin menu, toolbar and dashboard, and redirects the Posts list, Add New and edit screens to the Dashboard. Only the admin UI changes: the post type stays registered, and posts can still be created and edited in other ways, for example through the REST API.',
						'default'     => false,
					],
					'disable_frontend_admin_bar'       => [
						'type'        => 'checkbox',
						'group'       => 'Admin',
						'label'       => 'Disable frontend admin bar',
						'description' => 'Hides the frontend admin bar for users who do not match the exempt role or capability below.',
						'default'     => false,
					],
					'frontend_admin_bar_exempt_target' => [
						'type'        => 'key',
						'group'       => 'Admin',
						'label'       => 'Admin bar exempt role or capability',
						'description' => 'Role or capability that keeps the frontend admin bar. Default: administrator.',
						'default'     => 'administrator',
						'fallback_on_empty' => true,
					],
					'disable_auto_updates'             => [
						'type'        => 'checkbox',
						'group'       => 'Updates',
						'label'       => 'Disable automatic updates',
						'description' => 'Stops every automatic update, including WordPress core security releases, plugins, themes and translations. Every update then has to be installed by hand.',
						'default'     => false,
					],
					'disable_site_health'              => [
						'type'        => 'checkbox',
						'group'       => 'Admin',
						'label'       => 'Hide Site Health',
						'description' => 'Hides the Site Health menu item and dashboard widget, and redirects the Site Health screens to the Dashboard. Only the admin UI changes: Site Health checks still run in the background.',
						'default'     => false,
					],
					'remove_dashboard_clutter'         => [
						'type'        => 'checkbox',
						'group'       => 'Admin',
						'label'       => 'Remove default dashboard clutter',
						'description' => 'Removes the welcome panel and common stock dashboard widgets.',
						'default'     => false,
					],
					'excerpt_length_enabled'           => [
						'type'        => 'checkbox',
						'group'       => 'Content',
						'label'       => 'Override excerpt length',
						'description' => 'Applies the excerpt length value below.',
						'default'     => false,
					],
					'excerpt_length'                   => [
						'type'        => 'integer',
						'group'       => 'Content',
						'label'       => 'Excerpt length',
						'description' => 'Number of words used for the WordPress excerpt.',
						'default'     => 40,
						'min'         => 1,
						'max'         => 500,
					],
					'add_last_login_column'            => [
						'type'        => 'checkbox',
						'group'       => 'Admin',
						'label'       => 'Show last login column',
						'description' => 'Adds a sortable Last login column to the Users list. It records logins through a login form while this setting is on, so it is not a complete security log: logins with application passwords, for example, are not recorded.',
						'default'     => false,
					],
				],
			],
			'media' => [
				'title'       => 'Media Policies',
				'description' => 'Upload and image handling defaults for site builds.',
				'fields'      => [
					'custom_image_sizes_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Image Sizes',
						'label'       => 'Register custom image sizes',
						'description' => 'Adds custom image sizes and makes them selectable in the media modal.',
						'default'     => false,
					],
					'custom_image_widths'        => [
						'type'        => 'csv_int',
						'group'       => 'Image Sizes',
						'control'     => 'textarea',
						'rows'        => 6,
						'label'       => 'Custom image widths',
						'description' => 'One width per line or comma-separated. Height is automatic and aspect ratio is preserved.',
						'default'     => [480, 768, 960, 1440],
					],
					'remove_image_sizes_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Image Sizes',
						'label'       => 'Remove image sizes',
						'description' => 'Removes the specified image sizes from generation and selectors.',
						'default'     => false,
					],
					'removed_image_sizes'        => [
						'type'        => 'csv_string',
						'group'       => 'Image Sizes',
						'control'     => 'textarea',
						'rows'        => 12,
						'label'       => 'Removed image sizes',
						'description' => 'One per line or comma-separated. Applies to both intermediate and advanced image sizes.',
						'default'     => [
							'thumbnail',
							'medium',
							'medium_large',
							'large',
							'woocommerce_thumbnail',
							'woocommerce_single',
							'woocommerce_gallery_thumbnail',
							'bricks_large_16x9',
							'bricks_large',
							'bricks_large_square',
							'bricks_medium',
							'bricks_medium_square',
							'1536x1536',
							'2048x2048',
						],
					],
					'allow_font_uploads'         => [
						'type'        => 'checkbox',
						'group'       => 'Uploads',
						'label'       => 'Allow font uploads',
						'description' => 'Allows font file uploads in the media library.',
						'default'     => false,
					],
					'block_video_uploads'        => [
						'type'        => 'checkbox',
						'group'       => 'Uploads',
						'label'       => 'Block common video uploads',
						'description' => 'Removes MP4, M4V, MOV, WebM, AVI, MKV and WMV from the file types WordPress accepts for upload. Other video formats, and files added outside WordPress uploads such as over FTP, are not blocked.',
						'default'     => false,
					],
					'disable_image_compression'  => [
						'type'        => 'checkbox',
						'group'       => 'Uploads',
						'label'       => 'Disable image compression',
						'description' => 'Disables WordPress image compression for JPEG, WebP, and AVIF uploads.',
						'default'     => false,
					],
				],
			],
			'utils' => [
				'title'       => 'Utils',
				'description' => 'Opt-in public helper wrappers for templates, builders, and lightweight site code.',
				'fields'      => [
					'utils_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Loading',
						'label'       => 'Enable MAC Core utils',
						'description' => 'Required gate for the individual helper toggles below. Enabling this alone does not load any helpers.',
						'default'     => false,
					],
					'count_array_items_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Loading',
						'label'       => 'Enable Count Array Items helper',
						'description' => 'Loads the mac_core_count_array_items() wrapper.',
						'default'     => false,
					],
					'format_datetime_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Loading',
						'label'       => 'Enable Format Datetime helper',
						'description' => 'Loads the mac_core_format_datetime() wrapper.',
						'default'     => false,
					],
					'format_price_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Loading',
						'label'       => 'Enable Format Price helper',
						'description' => 'Loads the mac_core_format_price() wrapper.',
						'default'     => false,
					],
					'post_type_label_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Loading',
						'label'       => 'Enable Post Type Label helper',
						'description' => 'Loads the mac_core_get_post_type_label() wrapper.',
						'default'     => false,
					],
					'taxonomy_label_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Loading',
						'label'       => 'Enable Taxonomy Label helper',
						'description' => 'Loads the mac_core_get_taxonomy_label() wrapper.',
						'default'     => false,
					],
					'post_terms_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Loading',
						'label'       => 'Enable Post Terms helper',
						'description' => 'Loads the mac_core_get_post_terms() wrapper.',
						'default'     => false,
					],
					'plugin_status_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Loading',
						'label'       => 'Enable Plugin Status helper',
						'description' => 'Loads the mac_core_get_plugin_status() wrapper.',
						'default'     => false,
					],
					'theme_status_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Loading',
						'label'       => 'Enable Theme Status helper',
						'description' => 'Loads the mac_core_get_theme_status() wrapper.',
						'default'     => false,
					],
				],
			],
		];

		/**
		 * Filter the admin settings sections and fields.
		 *
		 * Add-ons should add new modules using the same array shape:
		 * `title`, `description`, and `fields`, where each field contains
		 * `type`, `label`, `description`, and `default`, and can set `group`
		 * to render under a subheading.
		 *
		 * Field types:
		 * - `checkbox`: true or false. An unchecked box is saved as false.
		 * - `text`: one line of text, saved with sanitize_text_field(). Unknown
		 *   types are treated as `text`.
		 * - `url`: a URL, saved with esc_url_raw(). An empty or invalid URL saves
		 *   `default`.
		 * - `key`: a role, capability or similar key, saved with sanitize_key().
		 *   With `fallback_on_empty`, an empty result saves `default`.
		 * - `integer`: a whole number, clamped to the optional `min` and `max`.
		 * - `csv_int`: a list of positive whole numbers.
		 * - `csv_string`: a list of lowercase tokens (a-z, 0-9, `_` and `-`).
		 * - `secret`: an API key, password or other secret. The form never prints
		 *   the stored value, and a blank submission keeps it. Secrets are
		 *   trimmed and stripped of control characters, and otherwise saved as
		 *   entered.
		 *
		 * List fields are entered one item per line or comma-separated. Set
		 * `control` to `textarea` to render them in a textarea `rows` lines high
		 * (default 5). When a list is submitted, `max_items` (default 500) limits
		 * how many unique items it keeps, and `max_item_length` (default 200
		 * characters) drops longer items. Stored lists are read as they are.
		 *
		 * Any field can set `sanitize_callback`, a callable that receives the
		 * submitted value on save (a string, or a bool for a checkbox) and returns
		 * the value to save. It is called with loose typing, as WordPress calls
		 * sanitize callbacks, and runs before the type's own sanitizing, which
		 * still applies to its result. A null return keeps the stored value. It
		 * does not run when settings are read, or for a blank `secret` submission.
		 *
		 * Add this filter when the add-on's plugin file loads. A section or field
		 * registered later still shows its stored values in the form, and saving
		 * keeps stored values that no section registers. Edits are only saved for
		 * sections registered before the form is saved on `admin_init`.
		 *
		 * @param array<string,array<string,mixed>> $sections Settings sections.
		 */
		$sections = \apply_filters( 'mac_core_settings_sections', $sections );

		return $this->normalize_sections( $sections );
	}

	/**
	 * Return default settings for all known modules.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function get_defaults(): array
	{
		$defaults = [];

		foreach ( $this->get_sections() as $module => $section ) {
			$defaults[ $module ] = [];

			foreach ( $section['fields'] as $field => $config ) {
				$defaults[ $module ][ $field ] = $config['default'];
			}
		}

		return $defaults;
	}

	/**
	 * Return one field configuration or null.
	 *
	 * @return array<string,mixed>|null
	 */
	public function get_field( string $module, string $field ): ?array
	{
		$sections = $this->get_sections();

		if ( ! isset( $sections[ $module ]['fields'][ $field ] ) ) {
			return null;
		}

		return $sections[ $module ]['fields'][ $field ];
	}

	/**
	 * Return the module keys edited on one admin tab.
	 *
	 * The Helpers tab edits `utils`; the Settings tab edits every other module.
	 *
	 * @return array<int,string>
	 */
	public function get_tab_modules( string $tab ): array
	{
		$modules = \array_keys( $this->get_sections() );

		return match ( $tab ) {
			'settings' => \array_values( \array_diff( $modules, ['utils'] ) ),
			'helpers'  => \array_values( \array_intersect( $modules, ['utils'] ) ),
			default    => [],
		};
	}

	/**
	 * Normalize filtered section data.
	 *
	 * @param array<string,mixed> $sections Section data.
	 * @return array<string,array<string,mixed>>
	 */
	private function normalize_sections( array $sections ): array
	{
		$normalized = [];

		foreach ( $sections as $module => $section ) {
			if ( ! \is_array( $section ) ) {
				continue;
			}

			$fields = $section['fields'] ?? [];

			if ( ! \is_array( $fields ) || $fields === [] ) {
				continue;
			}

			$normalized_fields = [];

			foreach ( $fields as $field => $config ) {
				if ( ! \is_array( $config ) ) {
					continue;
				}

				$normalized_fields[ (string) $field ] = [
					'type'        => (string) ( $config['type'] ?? 'text' ),
					'group'       => (string) ( $config['group'] ?? '' ),
					'control'     => (string) ( $config['control'] ?? '' ),
					'label'       => (string) ( $config['label'] ?? $field ),
					'description' => (string) ( $config['description'] ?? '' ),
					'default'     => $config['default'] ?? '',
					'fallback_on_empty' => (bool) ( $config['fallback_on_empty'] ?? false ),
					'rows'        => isset( $config['rows'] ) ? \max( 2, (int) $config['rows'] ) : 5,
					'min'         => isset( $config['min'] ) ? (int) $config['min'] : null,
					'max'         => isset( $config['max'] ) ? (int) $config['max'] : null,
					'max_items'   => isset( $config['max_items'] ) ? \max( 1, (int) $config['max_items'] ) : self::DEFAULT_MAX_ITEMS,
					'max_item_length'   => isset( $config['max_item_length'] ) ? \max( 1, (int) $config['max_item_length'] ) : self::DEFAULT_MAX_ITEM_LENGTH,
					'sanitize_callback' => isset( $config['sanitize_callback'] ) && \is_callable( $config['sanitize_callback'] ) ? $config['sanitize_callback'] : null,
				];
			}

			if ( $normalized_fields === [] ) {
				continue;
			}

			$normalized[ (string) $module ] = [
				'title'       => (string) ( $section['title'] ?? \ucwords( \str_replace( '_', ' ', (string) $module ) ) ),
				'description' => (string) ( $section['description'] ?? '' ),
				'fields'      => $normalized_fields,
			];
		}

		return $normalized;
	}
}
