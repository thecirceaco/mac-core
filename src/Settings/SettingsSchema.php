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
				'title'       => 'General',
				'description' => '',
				'fields'      => [
					'top_level_menu'                   => [
						'type'    => 'checkbox',
						'group'   => 'Plugin',
						'label'   => 'Top-level admin menu',
						'option'  => 'Show MAC Core as a top-level admin menu item',
						'default' => false,
					],
					'delete_data_on_uninstall'         => [
						'type'        => 'checkbox',
						'group'       => 'Plugin',
						'label'       => 'Delete plugin data',
						'option'      => 'Delete plugin data on uninstall',
						'description' => 'When the plugin is deleted, also remove its settings, its license details and the last login times it recorded.',
						'default'     => false,
					],
					'developer_branding_enabled'       => [
						'type'        => 'checkbox',
						'group'       => 'Branding',
						'label'       => 'Developer branding',
						'option'      => 'Show the developer credit in the page source and the admin footer',
						'description' => 'Adds "Built by Author @ Company URL" as a comment in the page source. In the admin footer, "Built by Author @ Company", with Company linking to the URL, replaces the WordPress text and version.',
						'default'     => false,
					],
					'developer_branding_author'        => [
						'type'    => 'text',
						'group'   => 'Branding',
						'label'   => 'Author',
						'default' => 'Mihai Circea',
					],
					'developer_branding_company'       => [
						'type'    => 'text',
						'group'   => 'Branding',
						'label'   => 'Company',
						'default' => 'Circea',
					],
					'developer_branding_url'           => [
						'type'    => 'url',
						'group'   => 'Branding',
						'label'   => 'Company URL',
						'default' => 'https://circea.co',
					],
					'comment_control_enabled'          => [
						'type'        => 'checkbox',
						'group'       => 'Comments',
						'label'       => 'Comment control',
						'option'      => 'Control comments with MAC Core',
						'description' => 'When off, MAC Core leaves comments alone and the settings below have no effect.',
						'default'     => false,
					],
					'comments_enabled'                 => [
						'type'        => 'checkbox',
						'group'       => 'Comments',
						'label'       => 'Allow comments',
						'option'      => 'Allow comments on the site',
						'description' => 'When off, comments and pings close on every post type, the Comments menu and toolbar item are hidden, and the Comments screen goes to the Dashboard. Comments aren\'t deleted: block themes, the Activity widget and the REST API can still show them. When on, comments closed on a post, or by WordPress on old posts, stay closed.',
						'default'     => true,
					],
					'comments_posts_enabled'           => [
						'type'    => 'checkbox',
						'group'   => 'Comments',
						'label'   => 'Posts',
						'option'  => 'Allow comments on posts',
						'default' => true,
					],
					'comments_pages_enabled'           => [
						'type'    => 'checkbox',
						'group'   => 'Comments',
						'label'   => 'Pages',
						'option'  => 'Allow comments on pages',
						'default' => true,
					],
					'disable_native_posts'            => [
						'type'        => 'checkbox',
						'group'       => 'Content',
						'label'       => 'Native Posts',
						'option'      => 'Hide native Posts in the admin',
						'description' => 'Hides the Posts menu with Categories and Tags, the New Post toolbar item and the Quick Draft widget, and sends the Posts screens to the Dashboard. The post type stays registered, so posts can still be changed in other ways, like the REST API.',
						'default'     => false,
					],
					'disable_frontend_admin_bar'       => [
						'type'        => 'checkbox',
						'group'       => 'Admin',
						'label'       => 'Front-end admin bar',
						'option'      => 'Hide the admin bar on the front end',
						'description' => 'Users with the role or capability below still see it.',
						'default'     => false,
					],
					'frontend_admin_bar_exempt_target' => [
						'type'        => 'key',
						'group'       => 'Admin',
						'label'       => 'Admin bar exception',
						'description' => 'The role or capability that keeps the front-end admin bar, like administrator or edit_posts. Left empty, it\'s administrator.',
						'default'     => 'administrator',
						'fallback_on_empty' => true,
					],
					'disable_auto_updates'             => [
						'type'        => 'checkbox',
						'group'       => 'Updates',
						'label'       => 'Automatic updates',
						'option'      => 'Disable automatic updates',
						'description' => 'Stops every automatic update: WordPress, including security releases, plugins, themes and translations. The auto-update links on the Plugins and Themes screens go away, and updates have to be installed by hand.',
						'default'     => false,
					],
					'disable_site_health'              => [
						'type'        => 'checkbox',
						'group'       => 'Admin',
						'label'       => 'Site Health',
						'option'      => 'Hide Site Health',
						'description' => 'Hides its menu item and dashboard widget, and sends its screens to the Dashboard. The checks still run in the background.',
						'default'     => false,
					],
					'remove_dashboard_clutter'         => [
						'type'        => 'checkbox',
						'group'       => 'Admin',
						'label'       => 'Dashboard',
						'option'      => 'Remove the Welcome panel and the default dashboard widgets',
						'description' => 'At a Glance, Activity, Quick Draft, and WordPress Events and News. Widgets that plugins add stay.',
						'default'     => false,
					],
					'excerpt_length_enabled'           => [
						'type'    => 'checkbox',
						'group'   => 'Content',
						'label'   => 'Excerpt length',
						'option'  => 'Override the excerpt length',
						'default' => false,
					],
					'excerpt_length'                   => [
						'type'        => 'integer',
						'group'       => 'Content',
						'label'       => 'Excerpt words',
						'description' => 'Words in automatic excerpts; WordPress uses 55. Excerpts written by hand stay as they are.',
						'default'     => 40,
						'min'         => 1,
						'max'         => 500,
					],
					'add_last_login_column'            => [
						'type'        => 'checkbox',
						'group'       => 'Admin',
						'label'       => 'Last login',
						'option'      => 'Show a Last login column in the Users list',
						'description' => 'Records each login through a login form while this is on, so it\'s not a full security log: logins with application passwords, for example, aren\'t recorded.',
						'default'     => false,
					],
				],
			],
			'media' => [
				'title'       => 'Media',
				'description' => '',
				'fields'      => [
					'custom_image_sizes_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Image Sizes',
						'label'       => 'Custom sizes',
						'option'      => 'Add an image size for each width below',
						'description' => 'Each size keeps the image\'s proportions and shows as mac-image-WIDTH in the media modal. Only new uploads get them; regenerate thumbnails for older images.',
						'default'     => false,
					],
					'custom_image_widths'        => [
						'type'        => 'csv_int',
						'group'       => 'Image Sizes',
						'control'     => 'textarea',
						'rows'        => 6,
						'label'       => 'Custom widths',
						'description' => 'In pixels, one per line or comma-separated.',
						'default'     => [480, 768, 960, 1440],
					],
					'remove_image_sizes_enabled' => [
						'type'        => 'checkbox',
						'group'       => 'Image Sizes',
						'label'       => 'Removed sizes',
						'option'      => 'Stop generating the image sizes below',
						'description' => 'New uploads don\'t get them, and the media modal doesn\'t offer them. Files already generated stay.',
						'default'     => false,
					],
					'removed_image_sizes'        => [
						'type'        => 'csv_string',
						'group'       => 'Image Sizes',
						'control'     => 'textarea',
						'rows'        => 12,
						'label'       => 'Sizes to remove',
						'description' => 'Size names, one per line or comma-separated. Theme and plugin sizes work too, like woocommerce_single.',
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
						'label'       => 'Font uploads',
						'option'      => 'Allow font uploads',
						'description' => 'The media library accepts TTF, OTF, WOFF, WOFF2 and EOT files.',
						'default'     => false,
					],
					'block_video_uploads'        => [
						'type'        => 'checkbox',
						'group'       => 'Uploads',
						'label'       => 'Video uploads',
						'option'      => 'Block common video uploads',
						'description' => 'WordPress no longer accepts MP4, M4V, MOV, WebM, AVI, MKV and WMV uploads. Other video formats, and files added another way, like over FTP, aren\'t blocked.',
						'default'     => false,
					],
					'disable_image_compression'  => [
						'type'        => 'checkbox',
						'group'       => 'Uploads',
						'label'       => 'Image compression',
						'option'      => 'Disable image compression',
						'description' => 'The images WordPress generates from JPEG, WebP and AVIF uploads are saved at full quality (100), so the files are larger.',
						'default'     => false,
					],
				],
			],
			'utils' => [
				'title'       => 'Helpers',
				'description' => 'Functions for templates and builders, like Bricks.',
				'fields'      => [
					'utils_enabled' => [
						'type'        => 'checkbox',
						'label'       => 'Helpers',
						'option'      => 'Load the helpers checked below',
						'description' => 'Needed for every helper below; on its own it loads none. Turning a helper off breaks the templates that call it.',
						'default'     => false,
					],
					'count_array_items_enabled' => [
						'type'        => 'checkbox',
						'label'       => 'Count array items',
						'option'      => 'Load mac_core_count_array_items()',
						'description' => 'Counts the items of an array saved in a post\'s meta, like a gallery or a relationship field.',
						'default'     => false,
					],
					'format_datetime_enabled' => [
						'type'        => 'checkbox',
						'label'       => 'Format datetime',
						'option'      => 'Load mac_core_format_datetime()',
						'description' => 'Formats date and time fields with presets, as text or HTML.',
						'default'     => false,
					],
					'format_price_enabled' => [
						'type'        => 'checkbox',
						'label'       => 'Format price',
						'option'      => 'Load mac_core_format_price()',
						'description' => 'Formats an amount as a price in a currency.',
						'default'     => false,
					],
					'post_type_label_enabled' => [
						'type'        => 'checkbox',
						'label'       => 'Post type label',
						'option'      => 'Load mac_core_get_post_type_label()',
						'description' => 'Returns a post type\'s singular or plural name.',
						'default'     => false,
					],
					'taxonomy_label_enabled' => [
						'type'        => 'checkbox',
						'label'       => 'Taxonomy label',
						'option'      => 'Load mac_core_get_taxonomy_label()',
						'description' => 'Returns a taxonomy\'s singular or plural name.',
						'default'     => false,
					],
					'post_terms_enabled' => [
						'type'        => 'checkbox',
						'label'       => 'Post terms',
						'option'      => 'Load mac_core_get_post_terms()',
						'description' => 'Returns a post\'s terms as text, links or spans.',
						'default'     => false,
					],
					'plugin_status_enabled' => [
						'type'        => 'checkbox',
						'label'       => 'Plugin status',
						'option'      => 'Load mac_core_get_plugin_status()',
						'description' => 'Tells whether a supported plugin, like ACF or WS Form, is active.',
						'default'     => false,
					],
					'theme_status_enabled' => [
						'type'        => 'checkbox',
						'label'       => 'Theme status',
						'option'      => 'Load mac_core_get_theme_status()',
						'description' => 'Tells whether Bricks or Etch is the active theme or its parent.',
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
		 * - `checkbox`: true or false. An unchecked box is saved as false. Set
		 *   `option` to the sentence shown next to the box; `description` then
		 *   shows below it. Without `option`, `description` shows next to the box.
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
					'option'      => (string) ( $config['option'] ?? '' ),
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
